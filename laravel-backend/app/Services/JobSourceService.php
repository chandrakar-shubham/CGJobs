<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobImport;
use App\Models\JobSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobSourceService
{
    public function sync(JobSource $source): array
    {
        $source->update(['last_fetched_at' => now(), 'last_error' => null]);

        try {
            $raw = $this->get($source->fetch_url);
            $items = match ($source->source_type) {
                'rss', 'blogger' => $this->parseRss($raw, $source),
                'json_api', 'rest_api', 'wordpress' => $this->parseJson($raw, $source),
                default => $this->parseListing($raw, $source),
            };

            $imported = 0;
            $skipped = 0;

            foreach ($items as $item) {
                if (empty($item['url']) || empty($item['title'])) {
                    $skipped++;
                    continue;
                }

                $url = $this->absoluteUrl($item['url'], $source->base_url);
                if (!$this->sameHost($url, $source->base_url)) {
                    $skipped++;
                    continue;
                }

                if (JobImport::where('job_source_id', $source->id)->where('external_url', $url)->exists()
                    || Job::where('source_url', $url)->exists()) {
                    $skipped++;
                    continue;
                }

                // For ordinary websites, fetch the actual article instead of storing only the listing title.
                if (!in_array($source->source_type, ['rss', 'blogger', 'json_api', 'rest_api', 'wordpress'], true)) {
                    try {
                        $detail = $this->parseDetail($this->get($url), $url);
                        $item = array_merge($item, array_filter($detail, fn ($v) => $v !== null && $v !== ''));
                    } catch (\Throwable $e) {
                        Log::warning('Job detail fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
                    }
                }

                $title = $this->cleanText($item['title'] ?? '');
                $content = $this->cleanText($item['content'] ?? '');
                $summary = $this->makeShortSummary($item['summary'] ?? $content ?: $title);
                $category = $item['category'] ?? $source->default_category ?? $this->detectCategory($title);
                $image = $item['image_url'] ?? null;

                // We deliberately do not reuse the source article's image. Every imported article gets
                // a unique, copyright-safe CGJobs SVG cover generated from its content/category.
                $image = $this->generateCover($title, $category, $url) ?: $image;

                $import = JobImport::create([
                    'job_source_id' => $source->id,
                    'external_key' => sha1($url),
                    'external_url' => $url,
                    'title' => Str::limit($title, 250, ''),
                    'summary' => $summary,
                    'content' => $content ?: $summary,
                    'category' => $category,
                    'image_url' => $image,
                    'published_at' => $item['published_at'] ?? null,
                    'raw_payload' => $item,
                    'status' => $source->publish_mode === 'auto' ? 'approved' : 'pending',
                    'fetched_at' => now(),
                ]);

                if ($source->publish_mode === 'auto') {
                    $this->publish($import);
                }
                $imported++;
            }

            $source->update([
                'last_success_at' => now(),
                'last_items_fetched' => count($items),
                'last_items_imported' => $imported,
                'last_items_skipped' => $skipped,
            ]);

            return ['fetched' => count($items), 'imported' => $imported, 'skipped' => $skipped];
        } catch (\Throwable $e) {
            $source->update(['last_error' => Str::limit($e->getMessage(), 1000, '')]);
            Log::error('Job source sync failed', ['source' => $source->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function publish(JobImport $import): Job
    {
        if ($import->job_id) {
            return $import->job;
        }

        $existing = Job::where('source_url', $import->external_url)->first();
        if ($existing) {
            $import->update(['job_id' => $existing->id, 'status' => 'published', 'processed_at' => now()]);
            return $existing;
        }

        $title = trim($import->title);
        $sourceContent = trim($import->content ?: $import->summary ?: $title);
        $summary = $this->makeShortSummary($import->summary ?: $sourceContent);
        $category = $import->category ?: 'CG Vyapam';
        $rewritten = $this->rephrase($title, $summary, $sourceContent, $category);

        $titleHi = $rewritten['title'] ?: $title;
        $summaryHi = $rewritten['summary'] ?: $summary;
        $contentHi = $rewritten['content'] ?: $sourceContent;
        $cover = $import->image_url ?: $this->generateCover($titleHi, $category, $import->external_url);

        $job = Job::create([
            'title' => $titleHi,
            'title_en' => $title,
            'summary' => $summaryHi,
            'summary_en' => $summary,
            'detailed_content' => $contentHi,
            'detailed_content_en' => $sourceContent,
            'category' => $category,
            'category_en' => $category,
            'section' => 'jobs',
            'post_type' => 'job',
            'source' => $import->source->name,
            'source_url' => $import->external_url,
            'image_url' => $cover,
            'published_at' => optional($import->published_at)->format('Y-m-d') ?: now()->format('Y-m-d'),
            'relative_time' => 'हाल ही में',
            'relative_time_en' => 'Recently',
            'is_new' => true,
            'application_start' => 'जारी',
            'last_date' => 'शीघ्र',
            'translation_status' => 'pending',
        ]);

        $import->update(['job_id' => $job->id, 'status' => 'published', 'processed_at' => now()]);

        // Keep the existing free translation pipeline for English app/site support.
        try {
            $t = app(TranslationService::class)->translateMany([
                'title' => $title,
                'summary' => $summary,
                'detailed' => $sourceContent,
                'category' => $category,
            ]);
            if (!empty($t['title'])) {
                $job->update([
                    'title' => $t['title'],
                    'summary' => $t['summary'] ?: $summaryHi,
                    'detailed_content' => $t['detailed'] ?: $contentHi,
                    'category' => $t['category'] ?: $category,
                    'translation_status' => 'ready',
                    'translation_error' => null,
                    'translated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Imported job translation failed', ['job' => $job->id, 'error' => $e->getMessage()]);
        }

        return $job;
    }

    private function rephrase(string $title, string $summary, string $content, string $category): array
    {
        $key = trim((string) env('GEMINI_API_KEY', ''));
        if ($key === '') {
            return [
                'title' => $title,
                'summary' => $this->makeShortSummary($summary),
                'content' => $this->fallbackRewrite($content),
            ];
        }

        try {
            $prompt = "You are the editorial engine for CGJobs, an Indian government-jobs information platform. Rewrite the supplied recruitment article in original wording. Do not invent facts, dates, vacancies, salary, eligibility, links or names. Preserve every factual detail that is present. Remove ads, promotional lines, social-media requests, navigation text and repeated boilerplate. Write in clear Hindi suitable for a job-seeker. Return JSON only with keys title, summary, content. summary must be 280-420 characters and content must be a clean, well-structured article with headings/bullets in plain text.\n\nCategory: {$category}\nTITLE: {$title}\nSUMMARY: {$summary}\nSOURCE ARTICLE:\n{$content}";

            $response = Http::timeout(45)->retry(2, 1000)->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='.urlencode($key), [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.2, 'responseMimeType' => 'application/json'],
            ])->throw()->json();

            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $json = json_decode(trim($text), true);
            if (is_array($json)) {
                return [
                    'title' => $this->cleanText($json['title'] ?? $title) ?: $title,
                    'summary' => $this->makeShortSummary($json['summary'] ?? $summary),
                    'content' => $this->cleanText($json['content'] ?? $content) ?: $content,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini rephrase failed; using safe fallback', ['error' => $e->getMessage()]);
        }

        return ['title' => $title, 'summary' => $this->makeShortSummary($summary), 'content' => $this->fallbackRewrite($content)];
    }

    private function fallbackRewrite(string $content): string
    {
        $content = $this->cleanText($content);
        return $content === '' ? '' : "भर्ती की मुख्य जानकारी\n\n".$content;
    }

    private function get(string $url): string
    {
        return Http::timeout(30)->retry(2, 1000)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; CGJobsBot/1.0; +https://cgjobs.app)',
            'Accept' => 'text/html,application/xhtml+xml,application/xml,application/json,text/plain;q=0.9,*/*;q=0.8',
        ])->get($url)->throw()->body();
    }

    private function parseListing(string $html, JobSource $source): array
    {
        $dom = $this->dom($html);
        $xpath = new \DOMXPath($dom);
        $out = [];
        foreach ($xpath->query('//a[@href]') as $a) {
            $title = $this->cleanText($a->textContent);
            $href = $this->absoluteUrl(trim($a->getAttribute('href')), $source->base_url);
            if (!$href || mb_strlen($title) < 12 || !$this->sameHost($href, $source->base_url)) continue;
            if (!preg_match('/(job|recruit|vacancy|bharti|rojgar|notification|recruitment|2026|2025|teacher|police|admit)/iu', $title.' '.$href)) continue;
            $out[$href] = ['url' => $href, 'title' => $title, 'summary' => $title, 'category' => $this->detectCategory($title)];
        }
        return array_slice(array_values($out), 0, 30);
    }

    private function parseRss(string $xml, JobSource $source): array
    {
        $simple = @simplexml_load_string($xml);
        if (!$simple) return [];
        $nodes = $simple->channel->item ?? $simple->entry ?? [];
        $out = [];
        foreach ($nodes as $n) {
            $url = (string)($n->link['href'] ?? $n->link);
            $title = $this->cleanText((string)$n->title);
            if (!$url || !$title) continue;
            $out[] = ['url' => $this->absoluteUrl($url, $source->base_url), 'title' => $title, 'summary' => $this->makeShortSummary((string)($n->description ?? $n->summary)), 'content' => $this->cleanText(strip_tags((string)($n->description ?? $n->summary))), 'category' => $this->detectCategory($title), 'published_at' => (string)($n->pubDate ?? $n->published)];
        }
        return array_slice($out, 0, 30);
    }

    private function parseJson(string $json, JobSource $source): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) return [];
        $rows = $data['items'] ?? $data['posts'] ?? $data['results'] ?? $data;
        if (!is_array($rows)) return [];
        $out = [];
        foreach ($rows as $n) {
            if (!is_array($n)) continue;
            $url = $n['url'] ?? $n['link'] ?? $n['permalink'] ?? null;
            $title = $n['title'] ?? $n['name'] ?? null;
            if (is_array($title)) $title = $title['rendered'] ?? '';
            if (!$url || !$title) continue;
            $out[] = ['url' => $this->absoluteUrl((string)$url, $source->base_url), 'title' => $this->cleanText((string)$title), 'summary' => $this->makeShortSummary(strip_tags((string)($n['excerpt'] ?? $n['description'] ?? ''))), 'content' => $this->cleanText(strip_tags((string)($n['content'] ?? $n['body'] ?? $n['description'] ?? ''))), 'category' => $this->detectCategory((string)$title), 'image_url' => $n['image'] ?? $n['featured_image'] ?? null, 'published_at' => $n['date'] ?? $n['published_at'] ?? null];
        }
        return array_slice($out, 0, 30);
    }

    private function parseDetail(string $html, string $url): array
    {
        $dom = $this->dom($html);
        $xpath = new \DOMXPath($dom);
        $this->removeNoise($xpath);

        $title = $this->meta($xpath, 'og:title') ?: $this->firstText($xpath, '//h1') ?: $this->firstText($xpath, '//title');
        $image = $this->meta($xpath, 'og:image');
        $date = $this->meta($xpath, 'article:published_time') ?: $this->firstAttr($xpath, '//time[@datetime]', 'datetime');

        $node = null;
        foreach (['//article', '//*[contains(concat(" ",normalize-space(@class)," ")," post-body ")]', '//*[contains(concat(" ",normalize-space(@class)," ")," entry-content ")]', '//*[contains(concat(" ",normalize-space(@class)," ")," post-content ")]', '//main'] as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length && mb_strlen($nodes->item(0)->textContent) > 250) { $node = $nodes->item(0); break; }
        }
        $node = $node ?: $dom->documentElement;
        $content = $this->nodeText($node);
        $fields = $this->extractRecruitmentFields($xpath, $content);

        return array_merge(['title' => $this->cleanText($title), 'content' => $content, 'summary' => $this->makeShortSummary($content), 'image_url' => $image, 'published_at' => $date, 'url' => $url], $fields);
    }

    private function extractRecruitmentFields(\DOMXPath $xpath, string $content): array
    {
        $out = [];
        $map = [
            'vacancies' => ['vacancy', 'total vacancy', 'total post', 'कुल पद'],
            'salary' => ['salary', 'pay scale', 'वेतन', 'मानदेय'],
            'eligibility' => ['qualification', 'educational qualification', 'eligibility', 'शैक्षणिक योग्यता', 'पात्रता'],
            'age_limit' => ['age limit', 'आयु सीमा'],
            'application_start' => ['start date', 'application start', 'आवेदन प्रारंभ'],
            'last_date' => ['last date', 'closing date', 'आवेदन की अंतिम तिथि'],
            'official_notification_url' => ['official notification', 'notification pdf', 'विज्ञापन'],
            'apply_url' => ['apply online', 'online application', 'आवेदन करें'],
        ];
        foreach ($map as $field => $labels) {
            foreach ($labels as $label) {
                $nodes = $xpath->query('//tr[th or td]');
                foreach ($nodes as $tr) {
                    $row = $this->cleanText($tr->textContent);
                    if (stripos($row, $label) !== false) {
                        $parts = preg_split('/\s{2,}|\s*[:|-]\s*/u', $row, 2);
                        if (isset($parts[1]) && trim($parts[1]) !== '') { $out[$field] = trim($parts[1]); break 2; }
                    }
                }
            }
        }
        return $out;
    }

    private function removeNoise(\DOMXPath $xpath): void
    {
        foreach ($xpath->query('//script|//style|//noscript|//iframe|//form|//nav|//footer|//header|//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"sidebar")]|//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"social")]|//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"advert")]') as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    private function nodeText(\DOMNode $node): string
    {
        $parts = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $this->cleanText($child->textContent);
                if ($text !== '') $parts[] = $text;
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($child->nodeName);
                $text = $this->nodeText($child);
                if ($text !== '') $parts[] = in_array($tag, ['p','div','li','tr','h2','h3','h4','br'], true) ? $text : $text;
            }
        }
        return $this->cleanText(implode("\n", $parts));
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;
        return trim($text);
    }

    private function makeShortSummary(string $text): string
    {
        $text = $this->cleanText($text);
        if (mb_strlen($text) <= 360) return $text;
        $cut = mb_substr($text, 0, 360);
        $pos = mb_strrpos($cut, ' ');
        return mb_substr($cut, 0, $pos ?: 360).'…';
    }

    private function generateCover(string $title, string $category, string $url): ?string
    {
        try {
            $hash = substr(sha1($url), 0, 16);
            $dir = public_path('generated/jobs');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $path = $dir.'/job-'.$hash.'.svg';
            if (is_file($path)) return '/generated/jobs/job-'.$hash.'.svg';
            $safeTitle = htmlspecialchars(Str::limit($title, 54, '…'), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $safeCategory = htmlspecialchars(Str::limit($category, 28, ''), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $h = hexdec(substr($hash, 0, 6)) % 360;
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="hsl('.$h.',72%,38%)"/><stop offset="1" stop-color="hsl('.(($h+48)%360).',72%,58%)"/></linearGradient></defs><rect width="1200" height="675" rx="42" fill="url(#g)"/><circle cx="1040" cy="120" r="150" fill="white" opacity=".10"/><circle cx="110" cy="590" r="210" fill="white" opacity=".08"/><rect x="70" y="70" width="1060" height="535" rx="34" fill="white" opacity=".10"/><text x="100" y="155" font-family="Arial,sans-serif" font-size="30" font-weight="700" fill="white">CGJOBS • GOVERNMENT JOB UPDATE</text><text x="100" y="250" font-family="Arial,sans-serif" font-size="54" font-weight="700" fill="white">'. $safeTitle .'</text><text x="100" y="330" font-family="Arial,sans-serif" font-size="28" fill="white" opacity=".92">'. $safeCategory .'</text><rect x="100" y="430" width="230" height="72" rx="36" fill="white" opacity=".18"/><text x="215" y="476" text-anchor="middle" font-family="Arial,sans-serif" font-size="26" font-weight="700" fill="white">READ MORE →</text></svg>';
            file_put_contents($path, $svg);
            return '/generated/jobs/job-'.$hash.'.svg';
        } catch (\Throwable $e) {
            Log::warning('Could not generate job cover', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function dom(string $html): \DOMDocument
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        return $dom;
    }

    private function meta(\DOMXPath $xpath, string $property): ?string
    {
        $nodes = $xpath->query('//meta[@property="'.$property.'"]/@content | //meta[@name="'.$property.'"]/@content');
        return ($nodes && $nodes->length) ? trim((string)$nodes->item(0)->nodeValue) : null;
    }

    private function firstText(\DOMXPath $xpath, string $selector): ?string
    {
        $nodes = $xpath->query($selector);
        return ($nodes && $nodes->length) ? $this->cleanText($nodes->item(0)->textContent) : null;
    }

    private function firstAttr(\DOMXPath $xpath, string $selector, string $attr): ?string
    {
        $nodes = $xpath->query($selector);
        return ($nodes && $nodes->length) ? trim((string)$nodes->item(0)->getAttribute($attr)) : null;
    }

    private function absoluteUrl(string $url, string $base): string
    {
        $url = trim($url);
        if ($url === '') return '';
        if (str_starts_with($url, '//')) return 'https:'.$url;
        if (filter_var($url, FILTER_VALIDATE_URL)) return $url;
        return rtrim($base, '/').'/'.ltrim($url, '/');
    }

    private function sameHost(string $url, string $base): bool
    {
        return strtolower((string)parse_url($url, PHP_URL_HOST)) === strtolower((string)parse_url($base, PHP_URL_HOST));
    }

    private function detectCategory(string $title): string
    {
        $t = mb_strtolower($title);
        if (str_contains($t, 'police')) return 'CG Police';
        if (str_contains($t, 'psc')) return 'CGPSC';
        if (preg_match('/teacher|school|education|shikshak|vyakhyata/u', $t)) return 'CG Education';
        if (preg_match('/health|doctor|nurse|hospital|nhm/u', $t)) return 'CG Health';
        if (preg_match('/central|ssc|railway/u', $t)) return 'Central Jobs';
        return 'CG Vyapam';
    }
}
