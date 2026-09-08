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
            'source' => 'google_news', 'geography' => 'india', 'strict_geo' => false,
        ]);

        $this->assertCount(1, $articles);
        $this->assertSame('Union Minister Bhupender Yadav outlines systemic reforms', $articles[0]['title']);
        $this->assertSame('The Union Minister discussed four strategic pillars of systemic reforms in India.', $articles[0]['description']);
        $this->assertSame('https://example.com/images/reforms.jpg', $articles[0]['image']);
        $this->assertSame('https://example.com/news/systemic-reforms', $articles[0]['url']);
    }

    public function test_google_news_rss_rejects_google_boilerplate_and_requires_real_image_and_description(): void
    {
        $feed = <<<'XML'
<?xml version="1.0"?><rss version="2.0"><channel>
<item><title>Google News</title><link>https://news.google.com/rss/articles/google</link><description>Comprehensive, up-to-date news coverage, aggregated from sources all over the world by Google News.</description></item>
<item><title>Short valid title - Example Source</title><link>https://news.google.com/rss/articles/short</link><description>A real article description with enough detail to be useful in the review queue.</description></item>
<item><title>Missing image story - Example Source</title><link>https://news.google.com/rss/articles/missing</link><description>A real article description but the page provides no usable image.</description></item>
<item><title>Good story - Example Source</title><link>https://news.google.com/rss/articles/good</link><description>Feed fallback description.</description></item>
</channel></rss>
XML;
        $shortPage = '<html><head><meta property="og:image" content="https://cdn.example.com/short.jpg"></head><body><p>Real page text.</p></body></html>';
        $missingPage = '<html><head><meta property="og:description" content="Real missing image article description with enough detail for review."></head><body></body></html>';
        $goodPage = '<html><head><meta property="og:description" content="Publisher description for a good story with enough detail."><meta property="og:image" content="https://cdn.example.com/good.jpg"></head></html>';
        Http::fake(function ($request) use ($feed, $shortPage, $missingPage, $goodPage) {
            if (str_contains($request->url(), 'news.google.com/rss/search')) return Http::response($feed, 200);
            if (str_contains($request->url(), '/short')) return Http::response($shortPage, 200);
            if (str_contains($request->url(), '/missing')) return Http::response($missingPage, 200);
            if (str_contains($request->url(), '/good')) return Http::response($goodPage, 200);
            return Http::response('', 404);
        });

        $articles = app(NewsRssFallback::class)->fetch('test query', 10, [
            'source' => 'google_news', 'geography' => 'india', 'strict_geo' => false,
        ]);

        $this->assertCount(2, $articles);
        $this->assertSame('Short valid title', $articles[0]['title']);
        $this->assertSame('https://cdn.example.com/short.jpg', $articles[0]['image']);
        $this->assertSame('Good story', $articles[1]['title']);
        $this->assertSame('https://cdn.example.com/good.jpg', $articles[1]['image']);
    }

    public function test_google_news_rss_keeps_collecting_until_requested_unique_limit(): void
    {
        $feed1 = <<<'XML'
<?xml version="1.0"?><rss version="2.0"><channel>
<item><title>Chhattisgarh cabinet approves new development plan - Source One</title><link>https://news.google.com/rss/articles/one</link><description>Chhattisgarh government approved a new development plan with major public investment details.</description></item>
<item><title>Chhattisgarh cabinet approves new development plan - Source One</title><link>https://news.google.com/rss/articles/duplicate</link><description>Duplicate story.</description></item>
</channel></rss>
XML;
        $feed2 = <<<'XML'
<?xml version="1.0"?><rss version="2.0"><channel>
<item><title>Raipur launches new public transport project - Source Two</title><link>https://news.google.com/rss/articles/two</link><description>Raipur announced a new public transport project with new routes and funding.</description></item>
<item><title>Bilaspur receives major infrastructure investment - Source Three</title><link>https://news.google.com/rss/articles/three</link><description>Bilaspur received new infrastructure investment across several development works.</description></item>
</channel></rss>
XML;
        $page = '<html><head><meta property="og:image" content="https://cdn.example.com/article.jpg"></head><body><p>Publisher article body with enough content to provide a useful description for review.</p></body></html>';
        Http::fake(function ($request) use ($feed1, $feed2, $page) {
            $url = $request->url();
            if (str_contains($url, 'q=Chhattisgarh%20latest%20news')) return Http::response($feed1, 200);
            if (str_contains($url, 'q=Chhattisgarh%20government%20schemes')) return Http::response($feed2, 200);
            return Http::response($page, 200);
        });
        $articles = app(NewsRssFallback::class)->fetch('Chhattisgarh latest news OR CGPSC OR CG Vyapam OR Chhattisgarh government', 3, [
            'source' => 'google_news', 'geography' => 'chhattisgarh', 'strict_geo' => false,
        ]);
        $this->assertCount(3, $articles);
        $this->assertSame('Chhattisgarh cabinet approves new development plan', $articles[0]['title']);
        $this->assertSame('Raipur launches new public transport project', $articles[1]['title']);
        $this->assertSame('Bilaspur receives major infrastructure investment', $articles[2]['title']);
    }
}
