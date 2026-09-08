<?php

namespace Tests\Feature;

use App\Services\NewsRssFallback;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsRssFallbackTest extends TestCase
{
    public function test_google_news_rss_recovers_publisher_description_image_and_canonical_url(): void
    {
        $feed = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel><item>
<title>Union Minister Bhupender Yadav says India is advancing systemic reforms - News On AIR</title>
<link>https://news.google.com/rss/articles/example123</link>
<description>News On AIR</description>
<pubDate>Tue, 08 Sep 2026 10:00:00 GMT</pubDate>
</item></channel></rss>
XML;
        $page = <<<'HTML'
<!doctype html><html><head>
<meta property="og:title" content="Union Minister Bhupender Yadav outlines systemic reforms" />
<meta property="og:description" content="The Union Minister discussed four strategic pillars of systemic reforms in India." />
<meta property="og:image" content="https://example.com/images/reforms.jpg" />
<link rel="canonical" href="https://example.com/news/systemic-reforms" />
</head><body><article>Article body</article></body></html>
HTML;
        Http::fake(function ($request) use ($feed, $page) {
            if (str_contains($request->url(), 'news.google.com/rss/search')) return Http::response($feed, 200);
            if (str_contains($request->url(), 'news.google.com/rss/articles/example123')) return Http::response($page, 200);
            return Http::response('', 404);
        });

        $articles = app(NewsRssFallback::class)->fetch('test query', 1, [
            'source' => 'google_news',
            'geography' => 'india',
            'strict_geo' => false,
        ]);

        $this->assertCount(1, $articles);
        $this->assertSame('Union Minister Bhupender Yadav outlines systemic reforms', $articles[0]['title']);
        $this->assertSame('The Union Minister discussed four strategic pillars of systemic reforms in India.', $articles[0]['description']);
        $this->assertSame('https://example.com/images/reforms.jpg', $articles[0]['image']);
        $this->assertSame('https://example.com/news/systemic-reforms', $articles[0]['url']);
    }

    public function test_google_news_rss_keeps_collecting_until_requested_unique_limit(): void
    {
        $feed1 = <<<'XML'
<?xml version="1.0"?><rss version="2.0"><channel>
<item><title>Chhattisgarh cabinet approves new development plan - Source One</title><link>https://news.google.com/rss/articles/one</link><description>Chhattisgarh government approved a new development plan.</description></item>
<item><title>Chhattisgarh cabinet approves new development plan - Source One</title><link>https://news.google.com/rss/articles/duplicate</link><description>Duplicate story.</description></item>
</channel></rss>
XML;
        $feed2 = <<<'XML'
<?xml version="1.0"?><rss version="2.0"><channel>
<item><title>Raipur launches new public transport project - Source Two</title><link>https://news.google.com/rss/articles/two</link><description>Raipur announced a new public transport project.</description></item>
<item><title>Bilaspur receives major infrastructure investment - Source Three</title><link>https://news.google.com/rss/articles/three</link><description>Bilaspur received new infrastructure investment.</description></item>
</channel></rss>
XML;
        Http::fake(function ($request) use ($feed1, $feed2) {
            $url=$request->url();
            if(str_contains($url,'q=Chhattisgarh+latest+news'))return Http::response($feed1,200);
            if(str_contains($url,'q=Chhattisgarh+government+schemes'))return Http::response($feed2,200);
            return Http::response('<html><head><meta name="description" content="Real article description" /></head></html>',200);
        });
        $articles=app(NewsRssFallback::class)->fetch('Chhattisgarh latest news OR CGPSC OR CG Vyapam OR Chhattisgarh government',3,['source'=>'google_news','geography'=>'chhattisgarh','strict_geo'=>false]);
        $this->assertCount(3,$articles);
        $this->assertSame('Chhattisgarh cabinet approves new development plan',$articles[0]['title']);
        $this->assertSame('Raipur launches new public transport project',$articles[1]['title']);
        $this->assertSame('Bilaspur receives major infrastructure investment',$articles[2]['title']);
    }
}
