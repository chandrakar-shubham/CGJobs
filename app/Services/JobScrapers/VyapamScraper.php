<?php

namespace App\Services\JobScrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VyapamScraper
{
    protected string $baseUrl = 'https://vyapamcg.cgstate.gov.in';
    protected array $endpoints = [
        'job' => 'https://vyapamcg.cgstate.gov.in/Posts?tag=ONLINEAPPLICATION',
        'admit_card' => 'https://vyapamcg.cgstate.gov.in/Posts?tag=ADMIT%20CARD',
        'answer_key' => 'https://vyapamcg.cgstate.gov.in/Posts?tag=MODEL%20ANSWERS',
        'result' => 'https://vyapamcg.cgstate.gov.in/Post?PostID=RESULT',
    ];

    /**
     * Scrape CGSSB (Vyapam) posts across categories
     */
    public function scrape(array $options = []): array
    {
        $records = [];
        $requestedTypes = !empty($options['post_types']) ? $options['post_types'] : array_keys($this->endpoints);

        foreach ($this->endpoints as $type => $url) {
            if (!in_array($type, $requestedTypes)) {
                continue;
            }

            try {
                $response = Http::timeout(12)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ])
                    ->get($url);

                if (!$response->successful()) {
                    Log::warning("Vyapam Scraper failed to fetch {$url}: Status " . $response->status());
                    continue;
                }

                $items = $this->parseHtml($response->body(), $type, $url);

                foreach ($items as $item) {
                    // Date filtering if provided
                    if (!empty($options['from']) && !empty($item['published_at'])) {
                        if ($item['published_at'] < $options['from']) continue;
                    }
                    if (!empty($options['to']) && !empty($item['published_at'])) {
                        if ($item['published_at'] > $options['to']) continue;
                    }

                    $records[] = $item;
                }
            } catch (\Throwable $e) {
                Log::error("Vyapam Scraper error on {$url}: " . $e->getMessage());
            }
        }

        return $records;
    }

    protected function parseHtml(string $html, string $type, string $sourceUrl): array
    {
        $items = [];
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Vyapam typically lists posts in cards or table views (views-row, cards, or tables)
        $cards = $xpath->query('//div[contains(@class,"views-row")] | //div[contains(@class,"post-item")] | //div[contains(@class,"card")] | //table//tr');

        if ($cards->length === 0) {
            // Fallback: search for anchor links with meaningful recruitment text
            $links = $xpath->query('//a[contains(@href,"/node/") or contains(@href,"/post/")]');
            foreach ($links as $link) {
                $text = trim(preg_replace('/\s+/', ' ', $link->textContent));
                if (strlen($text) < 10) continue;
                $href = $this->normalizeUrl($link->getAttribute('href'));
                $items[] = $this->formatItem($text, $text, $href, $type, null);
            }
            return $items;
        }

        foreach ($cards as $card) {
            $textNodes = $xpath->query('.//h2 | .//h3 | .//h4 | .//a | .//td', $card);
            $rawTitle = '';
            $linkUrl = null;
            $pdfUrl = null;

            foreach ($textNodes as $node) {
                $val = trim(preg_replace('/\s+/', ' ', $node->textContent));
                if (strlen($val) > strlen($rawTitle) && !str_starts_with($val, 'http')) {
                    $rawTitle = $val;
                }
                if ($node->nodeName === 'a' && $node->hasAttribute('href')) {
                    $href = $this->normalizeUrl($node->getAttribute('href'));
                    if (str_ends_with(strtolower($href), '.pdf')) {
                        $pdfUrl = $href;
                    } else {
                        $linkUrl = $linkUrl ?: $href;
                    }
                }
            }

            if (empty($rawTitle) || strlen($rawTitle) < 8) continue;

            $bodyText = trim(preg_replace('/\s+/', ' ', $card->textContent));
            $publishedAt = $this->extractDate($bodyText);

            $items[] = $this->formatItem($rawTitle, $bodyText, $linkUrl ?: $sourceUrl, $type, $pdfUrl, $publishedAt);
        }

        return $items;
    }

    protected function formatItem(string $title, string $rawText, string $url, string $type, ?string $pdfUrl = null, ?string $publishedAt = null): array
    {
        return [
            'source_key' => 'vyapam',
            'source_name' => 'छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (CG Vyapam)',
            'source_internal' => 'vyapamcg.cgstate.gov.in',
            'category' => 'CGSSB',
            'post_type' => $type,
            'title' => $title,
            'raw_content' => $rawText,
            'published_at' => $publishedAt ?: date('Y-m-d'),
            'official_notification_url' => $pdfUrl,
            'apply_url' => 'https://vyapamcg.cgstate.gov.in/online/',
            'official_website' => 'https://vyapamcg.cgstate.gov.in',
            'source_url' => $url,
            'department' => 'Other Departments',
        ];
    }

    protected function extractDate(string $text): ?string
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
