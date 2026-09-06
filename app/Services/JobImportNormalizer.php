<?php

namespace App\Services;

use App\Models\JobImport;
use Illuminate\Support\Facades\Http;

class JobImportNormalizer
{
    private const MAIN_CATEGORIES = ['CGSSB', 'CGPSC', 'Central Govt', 'Contractual'];

    private const DEPARTMENTS = [
        'Education' => ['education','teacher','shikshak','school','vyakhyata','lecturer','professor','samagra shiksha','sages','iti'],
        'Police' => ['police','constable','sub inspector','si ','home guard','nagar sena'],
        'Revenue' => ['revenue','rajasva','patwari','tehsildar','naib tehsildar'],
        'PHE' => ['phe','public health engineering','water supply','jal sansadhan'],
        'PWD' => ['pwd','public works','sub engineer','civil engineer','works department'],
        'Health' => ['health','doctor','nurse','nhm','hospital','medical','pharmacist','lab assistant','lab technician','dme','ayurved','cmho'],
        'Women & Child Development' => ['women','child development','anganwadi','wcd'],
        'Forest' => ['forest','van vibhag','wildlife','ranger','forest guard'],
        'Agriculture' => ['agriculture','krishi','agricultural','horticulture','animal husbandry','ahd'],
        'Panchayat' => ['panchayat','rural development','gram panchayat','zila panchayat'],
        'Transport' => ['transport','motor vehicle','rto','parivahan'],
    ];

    public function normalize(JobImport $import): JobImport
    {
        if (!$import->external_url) return $import;

        try {
            $html = Http::timeout(20)->retry(2, 500)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; CGJobsBot/1.0)',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->get($import->external_url)->throw()->body();

            [$title, $publisher, $pageText] = $this->extractTitleAndPublisher($html);
            $content = $this->removeSourceBranding($import->content ?: $import->summary ?: '');
            $summary = $this->removeSourceBranding($import->summary ?: $content);
            $jobCategory = $this->detectMainCategory(
                $title ?: $import->title,
                $import->external_url,
                $publisher,
                $content.' '.$summary.' '.$pageText,
                $import->job_category ?: null
            );
            $department = $this->detectDepartment(($title ?: $import->title).' '.$content.' '.$summary.' '.$pageText, $import->department ?: $import->category);

            $updates = [
                'job_category' => $jobCategory,
                'department' => $department,
                'category' => $department,
                'published_by' => $jobCategory,
            ];

            if ($title) $updates['title'] = $title;
            if ($summary) $updates['summary'] = $summary;
            if ($content) $updates['content'] = $content;

            $import->update($updates);

            if ($import->status === 'published' && $import->job) {
                $job = $import->job;
                $job->update([
                    'title' => $updates['title'] ?? $job->title,
                    'title_en' => $updates['title'] ?? $job->title_en,
                    'summary' => $updates['summary'] ?? $job->summary,
                    'summary_en' => $updates['summary'] ?? $job->summary_en,
                    'detailed_content' => $updates['content'] ?? $job->detailed_content,
                    'detailed_content_en' => $updates['content'] ?? $job->detailed_content_en,
                    'category' => $department,
                    'category_en' => $department,
                    'job_category' => $jobCategory,
                    'department' => $department,
                    'published_by' => $jobCategory,
                    'source' => 'CGJobs',
                    'source_url' => null,
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $import->fresh(['job', 'source']);
    }

    /**
     * Reclassify an already-imported item without downloading its source again.
     * This is used to repair existing pending imports after classifier changes.
     */
    public function reclassify(JobImport $import): JobImport
    {
        $title = (string) ($import->title ?? '');
        $content = $this->removeSourceBranding((string) ($import->content ?? ''));
        $summary = $this->removeSourceBranding((string) ($import->summary ?? ''));
        $jobCategory = $this->detectMainCategory(
            $title,
            (string) ($import->external_url ?? ''),
            null,
            $content.' '.$summary,
            $import->job_category ?: null
        );
        $department = $this->detectDepartment($title.' '.$content.' '.$summary, $import->department ?: $import->category);

        $import->update([
            'job_category' => $jobCategory,
            'published_by' => $jobCategory,
            'department' => $department,
            'category' => $department,
        ]);

        return $import->fresh(['job', 'source']);
    }

    private function extractTitleAndPublisher(string $html): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $titleCandidates = [];

        foreach ($xpath->query('//article//h1 | //main//h1 | //h1') as $node) {
            $titleCandidates[] = $this->cleanTitle($node->textContent);
        }

        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $json = json_decode($script->textContent, true);
            $this->collectJsonLdHeadlines($json, $titleCandidates);
        }

        foreach ($xpath->query('//meta[@property="og:title"] | //meta[@name="twitter:title"] | //title') as $node) {
            $titleCandidates[] = $this->cleanTitle($node->getAttribute('content') ?: $node->textContent);
        }

        $title = null;
        foreach ($titleCandidates as $candidate) {
            if ($this->isUsefulTitle($candidate)) {
                $title = $candidate;
                break;
            }
        }

        $body = $this->clean($dom->textContent);
        $publisher = null;
        if (preg_match('/(?:विभाग\s*का\s*नाम|department\s*name)\s*[:\-]+\s*(.*?)\s*(?:रिक्रूटमेंट\s*बोर्ड|recruitment\s*board|employment\s*type|वेतनमान|official\s*website|आधिकारिक\s*वेबसाइट)/iu', $body, $m)) {
            $publisher = $this->clean($m[1]);
        }

        return [$title, $publisher, $body];
    }

    private function detectMainCategory(
        string $title,
        string $url,
        ?string $publisher,
        string $body,
        ?string $existing = null
    ): string {
        // The article title and source URL are much more reliable than the
        // generic JobsKind page template. Classify them first so words such as
        // "vyapam" appearing in a site-wide menu cannot classify every post as CGSSB.
        $primary = mb_strtolower($title.' '.(string) $publisher.' '. $url);

        if (preg_match('/\b(cgpsc|chhattisgarh public service commission|public service commission)\b/iu', $primary)) {
            return 'CGPSC';
        }

        if (preg_match('/\b(cgssb|staff selection board|vyapam|cg vyapam)\b/iu', $primary)
            || preg_match('/कर्मचारी\s*चयन\s*बोर्ड|व्यावसायिक\s*परीक्षा|व्यवसायिक\s*परीक्षा/iu', $primary)) {
            return 'CGSSB';
        }

        if (preg_match('/\b(ssc|railway|secr|rrb|upsc|ibps|sbi|banking|defence|army|navy|air force|cisf|crpf|bsf|post office|india post|nit|iit|aiims|drdo|isro|central university|central government|central govt)\b/iu', $primary)) {
            return 'Central Govt';
        }

        if (preg_match('/\b(contractual|contract|samvida|samvida bharti|anubandh|outsourcing|walk[- ]?in)\b/iu', $primary)
            || preg_match('/संविदा|संविदाकर्मी|अनुबंध|आउटसोर्स/iu', $primary)) {
            return 'Contractual';
        }

        // Use the body only after title/URL signals. This avoids source-site
        // navigation and template text dominating classification.
        $secondary = mb_strtolower($body);

        if (preg_match('/\b(cgpsc|chhattisgarh public service commission|public service commission)\b/iu', $secondary)) return 'CGPSC';
        if (preg_match('/\b(cgssb|staff selection board|cg vyapam)\b/iu', $secondary) || preg_match('/कर्मचारी\s*चयन\s*बोर्ड/iu', $secondary)) return 'CGSSB';
        if (preg_match('/\b(ssc|railway|secr|rrb|upsc|ibps|sbi|banking|defence|army|navy|air force|cisf|crpf|bsf|post office|india post|nit|iit|aiims|drdo|isro|central university|central government|central govt)\b/iu', $secondary)) return 'Central Govt';
        if (preg_match('/\b(contractual|contract|samvida|samvida bharti|anubandh|outsourcing|walk[- ]?in)\b/iu', $secondary) || preg_match('/संविदा|संविदाकर्मी|अनुबंध|आउटसोर्स/iu', $secondary)) return 'Contractual';

        // Direct department/district recruitments without a board signal are
        // treated as contractual rather than incorrectly calling them CGSSB.
        if (in_array(trim((string) $existing), self::MAIN_CATEGORIES, true) && trim((string) $existing) !== 'CGSSB') {
            return trim((string) $existing);
        }

        return 'Contractual';
    }

    private function detectDepartment(string $text, ?string $hint = null): string
    {
        $haystack = mb_strtolower($text.' '.(string)$hint);
        foreach (self::DEPARTMENTS as $department => $keywords) {
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($haystack, mb_strtolower($keyword))) return $department;
            }
        }
        return 'Other Departments';
    }

    private function collectJsonLdHeadlines($value, array &$out): void
    {
        if (!is_array($value)) return;
        if (!empty($value['headline']) && is_string($value['headline'])) $out[] = $this->cleanTitle($value['headline']);
        foreach ($value as $child) $this->collectJsonLdHeadlines($child, $out);
    }

    private function cleanTitle(?string $title): ?string
    {
        $title = $this->clean((string)$title);
        if ($title === '') return null;
        $title = preg_replace('/^Jobskind(?:\.com)?\s*[:\-|]\s*/iu', '', $title);
        $title = preg_replace('/\s*[\-|:]\s*Jobskind(?:\.com)?\s*$/iu', '', $title);
        $title = preg_replace('/\s*[\-|:]\s*(?:Employment News|Latest Jobs|Job Updates)\s*$/iu', '', $title);
        return trim($title);
    }

    private function isUsefulTitle(?string $title): bool
    {
        if (!$title || mb_strlen($title) < 12) return false;
        if (preg_match('/^(jobskind(?:\.com)?|jobs?kind\.com|jobs?kind)$/iu', trim($title))) return false;
        if (preg_match('/^(home|latest jobs|jobs|recruitment|employment news)$/iu', trim($title))) return false;
        return (bool)preg_match('/(recruit|bharti|भर्ती|vacancy|पद|notification|नोटिफिकेशन|assistant|teacher|officer|staff|constable|admit|result|internship)/iu', $title);
    }

    private function removeSourceBranding(string $text): string
    {
        $text = preg_replace('/[^.!?\n]*(?:jobskind(?:\.com)?|jobs\s*kind(?:\.com)?)[^.!?\n]*[.!?]?/iu', ' ', $text);
        $text = preg_replace('/©\s*\d{4}[^\n]*/u', ' ', $text);
        $text = preg_replace('/(?:source|स्रोत)\s*[:\-]?\s*https?:\/\/[^\s]+/iu', ' ', $text);
        $text = preg_replace('/\s{2,}/u', ' ', $text);
        return trim($text);
    }

    private function clean(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}
