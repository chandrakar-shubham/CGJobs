<?php

namespace App\Services\JobScrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CgpscScraper
{
    protected string $baseUrl = 'https://psc.cg.gov.in';
    protected array $endpoints = [
        'advertisements' => 'https://psc.cg.gov.in/Advertisement.php',
        'notifications'  => 'https://psc.cg.gov.in/Notifications.php',
    ];

    /**
     * Scrape CGPSC Advertisements and Notifications
     *
     * @param array $options Filter options: ['from' => 'YYYY-MM-DD', 'to' => 'YYYY-MM-DD', 'post_types' => []]
     * @return array Standardized raw scraped records
     */
    public function scrape(array $options = []): array
    {
        $records = [];

        foreach ($this->endpoints as $type => $url) {
            try {
                $response = Http::timeout(12)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ])
                    ->get($url);

                if (!$response->successful()) {
                    Log::warning("CGPSC Scraper failed to fetch {$url}: Status " . $response->status());
                    continue;
                }

                $html = $response->body();
                $items = $this->parseTableRows($html, $type);
                
                foreach ($items as $item) {
                    // Date filtering if provided
                    if (!empty($options['from']) && !empty($item['published_at'])) {
                        if ($item['published_at'] < $options['from']) continue;
                    }
                    if (!empty($options['to']) && !empty($item['published_at'])) {
                        if ($item['published_at'] > $options['to']) continue;
                    }

                    // Post type filtering if requested
                    if (!empty($options['post_types']) && !in_array($item['post_type'], $options['post_types'])) {
                        continue;
                    }

                    $records[] = $item;
                }
            } catch (\Throwable $e) {
                Log::error("CGPSC Scraper error on {$url}: " . $e->getMessage());
            }
        }

        return $records;
    }

    /**
     * Parse HTML table on CGPSC page
     */
    protected function parseTableRows(string $html, string $type): array
    {
        $items = [];
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        // Find rows in table
        $rows = $xpath->query('//table//tr');

        foreach ($rows as $row) {
            $cols = $xpath->query('.//td', $row);
            if ($cols->length < 2) continue;

            $dateText = trim($cols->item(0)->textContent);
            $parsedDate = $this->parseDate($dateText);

            $titleCol = $cols->item(1);
            $rawTitle = trim(preg_replace('/\s+/', ' ', $titleCol->textContent));

            if (empty($rawTitle) || strlen($rawTitle) < 5) continue;

            // Extract PDF / link
            $linkNodes = $xpath->query('.//a[@href]', $row);
            $pdfUrl = null;
            foreach ($linkNodes as $linkNode) {
                $href = trim($linkNode->getAttribute('href'));
                if (!empty($href)) {
                    $pdfUrl = $this->normalizeUrl($href);
                    break;
                }
            }

            // Determine post type (job, admit_card, answer_key, result)
            $postType = 'job';
            $lowerTitle = mb_strtolower($rawTitle);
            if (str_contains($lowerTitle, 'admit card') || str_contains($lowerTitle, 'प्रवेश पत्र')) {
                $postType = 'admit_card';
            } elseif (str_contains($lowerTitle, 'model answer') || str_contains($lowerTitle, 'amended model answer') || str_contains($lowerTitle, 'उत्तर कुंजी')) {
                $postType = 'answer_key';
            } elseif (str_contains($lowerTitle, 'result') || str_contains($lowerTitle, 'चयन सूची') || str_contains($lowerTitle, 'merit')) {
                $postType = 'result';
            } elseif ($type === 'notifications' && !str_contains($lowerTitle, 'advertisement') && !str_contains($lowerTitle, 'विज्ञापन')) {
                $postType = 'admit_card'; // Default notifications to exam notice / admit
            }

            $items[] = [
                'source_key' => 'cgpsc',
                'source_name' => 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC)',
                'source_internal' => 'psc.cg.gov.in',
                'category' => 'CGPSC',
                'post_type' => $postType,
                'title' => $rawTitle,
                'raw_content' => $rawTitle . ($parsedDate ? " (दिनांक: {$parsedDate})" : ''),
                'published_at' => $parsedDate ?: date('Y-m-d'),
                'official_notification_url' => $pdfUrl,
                'apply_url' => 'https://psc.cg.gov.in/OnlineApp.htm',
                'official_website' => 'https://psc.cg.gov.in',
                'source_url' => $pdfUrl ?: $this->endpoints[$type],
                'department' => 'Other Departments',
            ];
        }

        return $items;
    }

    protected function parseDate(string $text): ?string
    {
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        return null;
    }

    protected function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
