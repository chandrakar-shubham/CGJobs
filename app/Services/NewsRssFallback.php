<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewsRssFallback
{
    /**
     * Fetch public RSS feeds when configured news APIs return too few results.
     * No API key is required.
     */
    public function fetch(string $query, int $limit): array
    {
        $queries = [$query];

        // The default CG feed benefits from several focused searches because a single
        // Google News RSS query can legitimately return only a handful of matching items.
        if (str_contains(strtolower($query), 'chhattisgarh') &&
            str_contains(strtolower($query), 'cgpsc') &&
            str_contains(strtolower($query), 'vyapam')) {
            $queries = [
                'Chhattisgarh latest news',
                'Chhattisgarh government jobs recruitment',
                'CGPSC latest',
                'CG Vyapam latest',
                'Chhattisgarh government schemes',
            ];
        }

        $articles = [];

        foreach ($queries as $searchQuery) {
            if (count($articles) >= $limit) {
                break;
            }

            $feedUrl = 'https://news.google.com/rss/search?q=' . rawurlencode($searchQuery . ' when:1d') . '&hl=en-IN&gl=IN&ceid=IN:en';

            try {
                $response = Http::timeout(15)->get($feedUrl);
                if (!$response->successful()) {
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->channel->item)) {
                    continue;
                }

                foreach ($xml->channel->item as $item) {
                    if (count($articles) >= $limit) {
                        break;
                    }

                    $title = trim((string) ($item->title ?? ''));
                    $url = trim((string) ($item->link ?? ''));
                    if ($title === '') {
                        continue;
                    }

                    $source = 'Google News';
                    $description = trim(strip_tags((string) ($item->description ?? '')));
                    $publishedAt = trim((string) ($item->pubDate ?? '')) ?: null;

                    // Google News RSS titles commonly end with " - Publisher".
                    if (str_contains($title, ' - ')) {
                        [$cleanTitle, $publisher] = array_pad(explode(' - ', $title, 2), 2, '');
                        if (trim($publisher) !== '') {
                            $title = trim($cleanTitle);
                            $source = trim($publisher);
                        }
                    }

                    $articles[] = [
                        'title' => $title,
                        'description' => $description ?: $title,
                        'source' => $source,
                        'url' => $url,
                        'image' => null,
                        'published_at' => $publishedAt,
                    ];
                }
            } catch (\Throwable) {
                // RSS is a fallback; a feed failure must not stop ingestion.
            }
        }

        return $articles;
    }
}
