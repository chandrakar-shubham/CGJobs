<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewsRssFallback
{
    public function fetch(string $query, int $limit, array $filters = []): array
    {
        $source = $filters['source'] ?? 'google_news';
        $geo = $filters['geography'] ?? 'chhattisgarh';
        $topic = trim((string) ($filters['topic'] ?? ''));
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $location = ['chhattisgarh'=>'Chhattisgarh','india'=>'India','world'=>'international world'][$geo] ?? 'Chhattisgarh';

        $articles = [];
        if ($source === 'google_trending') {
            $geoCode = $geo === 'chhattisgarh' ? 'IN' : ($geo === 'world' ? 'US' : 'IN');
            $feedUrls = ['https://trends.google.com/trending/rss?geo=' . $geoCode];
        } else {
            $queries = [];
            if ($topic) {
                $topicQueries = [
                    'national'=>'India latest national news', 'international'=>'international world latest news',
                    'economy'=>'India economy banking finance', 'environment'=>'India environment climate ecology',
                    'science'=>'India science technology ISRO', 'defence'=>'India defence military',
                    'polity'=>'India government polity governance', 'education'=>'India education schools universities',
                    'sports'=>'India sports latest', 'awards'=>'India awards appointments',
                    'reports'=>'India reports indexes rankings', 'important_days'=>'important days India',
                    'chhattisgarh'=>'Chhattisgarh latest news government', 'cgpsc'=>'CGPSC latest',
                    'cg_vyapam'=>'CG Vyapam latest',
                ];
                $queries[] = $topicQueries[strtolower(str_replace([' & ',' '], ['_','_'], $topic))] ?? ($location . ' ' . $topic . ' latest news');
            } else {
                $queries[] = $query;
            }
            if (count($queries) === 1 && strtolower($query) === strtolower('Chhattisgarh latest news OR CGPSC OR CG Vyapam OR Chhattisgarh government')) {
                $queries = ['Chhattisgarh latest news','Chhattisgarh government schemes','CGPSC latest','CG Vyapam latest','Chhattisgarh economy development'];
            }
            if (!$topic) $queries[] = $location . ' latest current affairs';
            $feedUrls = [];
            foreach (array_unique($queries) as $q) {
                $datePart = ' when:1d';
                if ($from) $datePart .= ' after:' . substr($from, 0, 10);
                if ($to) $datePart .= ' before:' . date('Y-m-d', strtotime($to . ' +1 day'));
                $feedUrls[] = 'https://news.google.com/rss/search?q=' . rawurlencode($q . $datePart) . '&hl=en-IN&gl=IN&ceid=IN:en';
            }
        }

        foreach ($feedUrls as $feedUrl) {
            if (count($articles) >= $limit) break;
            try {
                $response = Http::timeout(15)->get($feedUrl);
                if (!$response->successful()) continue;
                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->channel->item)) continue;
                foreach ($xml->channel->item as $item) {
                    if (count($articles) >= $limit) break;
                    $title = trim((string) ($item->title ?? ''));
                    $url = trim((string) ($item->link ?? ''));
                    if ($title === '') continue;
                    $sourceName = 'Google News';
                    $description = trim(strip_tags((string) ($item->description ?? '')));
                    $publishedAt = trim((string) ($item->pubDate ?? '')) ?: null;
                    if (str_contains($title, ' - ')) {
                        [$cleanTitle, $publisher] = array_pad(explode(' - ', $title, 2), 2, '');
                        if (trim($publisher) !== '') { $title = trim($cleanTitle); $sourceName = trim($publisher); }
                    }
                    $image = null;
                    if (isset($item->enclosure['url'])) $image = (string) $item->enclosure['url'];
                    $articles[] = ['title'=>$title,'description'=>$description ?: $title,'source'=>$sourceName,'url'=>$url,'image'=>$image,'published_at'=>$publishedAt];
                }
            } catch (\Throwable) {}
        }
        return $articles;
    }
}
