<?php

namespace App\Services\JobScrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CentralGovtScraper
{
    protected string $endpoint = 'https://www.freejobalert.com/government-jobs/';

    /**
     * Scrape Central Government job openings
     */
    public function scrape(array $options = []): array
    {
        $records = [];

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($this->endpoint);

            if (!$response->successful()) {
                Log::warning("Central Govt Scraper failed: Status " . $response->status());
                return [];
            }

            $items = $this->parseHtml($response->body());

            foreach ($items as $item) {
                if (!empty($options['from']) && !empty($item['published_at'])) {
                    if ($item['published_at'] < $options['from']) continue;
                }
                if (!empty($options['to']) && !empty($item['published_at'])) {
                    if ($item['published_at'] > $options['to']) continue;
                }
                $records[] = $item;
            }
        } catch (\Throwable $e) {
            Log::error("Central Govt Scraper error: " . $e->getMessage());
        }

        return $records;
    }

    protected function parseHtml(string $html): array
    {
        $items = [];
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $rows = $xpath->query('//table//tr');

        foreach ($rows as $row) {
            $cols = $xpath->query('.//td', $row);
            if ($cols->length < 3) continue;

            $postDateText = trim($cols->item(0)->textContent);
            $recruitmentBoard = trim($cols->item(1)->textContent);
            $postName = trim($cols->item(2)->textContent);
            $qualification = $cols->length > 3 ? trim($cols->item(3)->textContent) : '';
            $advtNo = $cols->length > 4 ? trim($cols->item(4)->textContent) : '';
            $lastDateText = $cols->length > 5 ? trim($cols->item(5)->textContent) : '';

            // Extract link
            $link = null;
            $links = $xpath->query('.//a[@href]', $row);
            foreach ($links as $l) {
                $href = $l->getAttribute('href');
                if (!empty($href) && !str_contains($href, 'javascript:')) {
                    $link = $href;
                    break;
                }
            }

            $rawTitle = "{$recruitmentBoard} - {$postName}";
            if (strlen($rawTitle) < 8) continue;

            $publishedAt = $this->extractDate($postDateText);
            $lastDate = $this->extractDate($lastDateText);

            $rawContent = "भर्ती बोर्ड: {$recruitmentBoard}\nपद नाम: {$postName}\nयोग्यता: {$qualification}\nअंतिम तिथि: {$lastDateText}";

            $items[] = [
                'source_key' => 'central_govt',
                // STRICT RULE: Mask source - NEVER mention FreeJobAlert publicly
                'source_name' => 'भारत सरकार भर्ती (Central Government)',
                'source_internal' => 'freejobalert.com/government-jobs',
                'category' => 'CENTRAL GOVT',
                'post_type' => 'job',
                'title' => $rawTitle,
                'raw_content' => $rawContent,
                'published_at' => $publishedAt ?: date('Y-m-d'),
                'last_date' => $lastDate,
                'qualification_summary' => $qualification,
                'official_notification_url' => $link,
                'apply_url' => $link,
                'official_website' => 'https://www.india.gov.in',
                'source_url' => $link ?: $this->endpoint,
                'department' => $this->detectDepartment($recruitmentBoard . ' ' . $postName),
            ];
        }

        return $items;
    }

    protected function detectDepartment(string $text): string
    {
        $lower = mb_strtolower($text);
        if (str_contains($lower, 'defence') || str_contains($lower, 'army') || str_contains($lower, 'navy') || str_contains($lower, 'air force') || str_contains($lower, 'crpf') || str_contains($lower, 'bsf') || str_contains($lower, 'cisf') || str_contains($lower, 'police')) {
            return 'Police';
        }
        if (str_contains($lower, 'bank') || str_contains($lower, 'ibps') || str_contains($lower, 'sbi') || str_contains($lower, 'rbi')) {
            return 'Banking';
        }
        if (str_contains($lower, 'railway') || str_contains($lower, 'rrb') || str_contains($lower, 'rcell')) {
            return 'PWD';
        }
        if (str_contains($lower, 'teaching') || str_contains($lower, 'professor') || str_contains($lower, 'teacher') || str_contains($lower, 'kvs') || str_contains($lower, 'nvs') || str_contains($lower, 'university')) {
            return 'Education';
        }
        if (str_contains($lower, 'health') || str_contains($lower, 'hospital') || str_contains($lower, 'aiims') || str_contains($lower, 'medical') || str_contains($lower, 'nurse')) {
            return 'Health';
        }
        return 'General';
    }

    protected function extractDate(string $text): ?string
    {
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        if (preg_match('/(\d{1,2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+(\d{4})/i', $text, $m)) {
            return date('Y-m-d', strtotime("{$m[1]} {$m[2]} {$m[3]}"));
        }
        return null;
    }
}
