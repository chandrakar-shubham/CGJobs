<?php

namespace App\Services\JobScrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JobskindScraper
{
    protected string $feedUrl = 'https://jobskind.com/category/chhattisgarh-govt-jobs/feed/';
    protected string $fallbackFeedUrl = 'https://jobskind.com/feed/';

    /**
     * Scrape Jobskind as a secondary fallback for Chhattisgarh jobs
     */
    public function scrape(array $options = []): array
    {
        $records = [];
        $xmlContent = null;

        // Try category feed first, then fallback
        foreach ([$this->feedUrl, $this->fallbackFeedUrl] as $url) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CGExamBot/2.0',
                        'Accept' => 'application/rss+xml, application/xml, text/xml',
                    ])
                    ->get($url);

                if ($response->successful()) {
                    $xmlContent = $response->body();
                    break;
                }
            } catch (\Throwable $e) {
                Log::warning("Jobskind feed fetch failed for {$url}: " . $e->getMessage());
            }
        }

        if (!$xmlContent) {
            return [];
        }

        $xml = @simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml || !isset($xml->channel->item)) {
            return [];
        }

        foreach ($xml->channel->item as $item) {
            $rawTitle = trim((string)$item->title);
            $link = trim((string)$item->link);
            $description = strip_tags((string)$item->description);
            $pubDate = isset($item->pubDate) ? date('Y-m-d', strtotime((string)$item->pubDate)) : date('Y-m-d');

            if (empty($rawTitle) || strlen($rawTitle) < 8) continue;

            // Date filtering
            if (!empty($options['from']) && $pubDate < $options['from']) continue;
            if (!empty($options['to']) && $pubDate > $options['to']) continue;

            // Determine proper official category & authority (NEVER show Jobskind)
            $classification = $this->classify($rawTitle . ' ' . $description);

            // Filter out if user selected specific category and this item doesn't match
            if (!empty($options['categories']) && !in_array($classification['category'], $options['categories'])) {
                continue;
            }

            $records[] = [
                'source_key' => 'jobskind',
                // STRICT MASKING: Never show Jobskind to users or search engines
                'source_name' => $classification['official_source'],
                'source_internal' => 'jobskind.com',
                'category' => $classification['category'],
                'post_type' => 'job',
                'title' => $this->cleanTitle($rawTitle),
                'raw_content' => $description,
                'published_at' => $pubDate,
                'official_notification_url' => null,
                'apply_url' => $link,
                'official_website' => $classification['official_website'],
                'source_url' => $link,
                'department' => $classification['department'],
            ];
        }

        return $records;
    }

    protected function classify(string $text): array
    {
        $lower = mb_strtolower($text);

        if (str_contains($lower, 'psc') || str_contains($lower, 'लोक सेवा आयोग')) {
            return [
                'category' => 'CGPSC',
                'official_source' => 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC)',
                'official_website' => 'https://psc.cg.gov.in',
                'department' => $this->detectDepartment($text),
            ];
        }

        if (str_contains($lower, 'vyapam') || str_contains($lower, 'व्यापम') || str_contains($lower, 'cgssb')) {
            return [
                'category' => 'CGSSB',
                'official_source' => 'छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (CG Vyapam)',
                'official_website' => 'https://vyapamcg.cgstate.gov.in',
                'department' => $this->detectDepartment($text),
            ];
        }

        // Default to Contractual / District recruitment
        return [
            'category' => 'CONTRACTUAL',
            'official_source' => 'शासकीय भर्ती (छत्तीसगढ़ शासन / जिला कलेक्टोरेट)',
            'official_website' => 'https://erojgar.cg.gov.in',
            'department' => $this->detectDepartment($text),
        ];
    }

    protected function detectDepartment(string $text): string
    {
        $lower = mb_strtolower($text);
        if (str_contains($lower, 'shikshak') || str_contains($lower, 'teacher') || str_contains($lower, 'school') || str_contains($lower, 'शिक्षा')) {
            return 'Education';
        }
        if (str_contains($lower, 'health') || str_contains($lower, 'doctor') || str_contains($lower, 'nurse') || str_contains($lower, 'nhm') || str_contains($lower, 'स्वास्थ्य')) {
            return 'Health';
        }
        if (str_contains($lower, 'cseb') || str_contains($lower, 'csphcl') || str_contains($lower, 'बिजली') || str_contains($lower, 'विद्युत')) {
            return 'CSEB';
        }
        if (str_contains($lower, 'pwd') || str_contains($lower, 'लोक निर्माण') || str_contains($lower, 'सिंचाई') || str_contains($lower, 'engineer')) {
            return 'PWD';
        }
        if (str_contains($lower, 'police') || str_contains($lower, 'पुलिस') || str_contains($lower, 'constable') || str_contains($lower, 'आरक्षक')) {
            return 'Police';
        }
        if (str_contains($lower, 'patwari') || str_contains($lower, 'पटवारी') || str_contains($lower, 'राजस्व') || str_contains($lower, 'कलेक्टोरेट')) {
            return 'Revenue';
        }
        return 'Other Departments';
    }

    protected function cleanTitle(string $title): string
    {
        // Remove branding like "Jobskind", "Jobs Kind", etc.
        $title = preg_replace('/(?:\s*[-–|]\s*)?(?:Jobskind|Jobs\s*kind|Jobskind\.com)/i', '', $title);
        return trim($title);
    }
}
