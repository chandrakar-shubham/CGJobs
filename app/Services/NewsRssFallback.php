<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewsRssFallback
{
    /**
     * Fetch public RSS feeds when the configured news APIs are unavailable.
     * No API key is required for these feeds.
     */
    public function fetch(string $query, int $limit): array
    {
        $feeds = [
            'https://news.google.com/rss/search?q=' . rawurlencode($query . ' when:1d') . '&hl=en-IN&gl=IN&ceid=IN:en',
        ];

        $articles = [];

        foreach ($feeds as $feedUrl) {
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

                    if (count($articles) >= $limit) {
                        return $articles;
                    }
                }
            } catch (\Throwable) {
                // RSS is a fallback; a feed failure must not stop the pipeline.
            }
        }

        return $articles;
    }
}
