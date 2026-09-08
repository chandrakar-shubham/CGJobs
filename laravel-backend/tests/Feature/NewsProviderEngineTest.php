<?php

namespace Tests\Feature;

use App\Services\NewsProviderEngine;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsProviderEngineTest extends TestCase
{
    public function test_saurav_newsapi_mirror_uses_documented_category_country_endpoint_and_preserves_article_fields(): void
    {
        config()->set('services.newsdata.key', '');
        config()->set('services.newsapi.key', '');
        Http::fake([
            'https://saurav.tech/NewsAPI/top-headlines/category/sports/in.json' => Http::response([
                'status' => 'ok',
                'articles' => [[
                    'source' => ['id' => null, 'name' => 'Example Sports'],
                    'title' => 'India wins important cricket match - Example Sports',
                    'description' => 'India won an important cricket match in a closely contested game.',
                    'url' => 'https://example.com/article',
                    'urlToImage' => 'https://example.com/image.jpg',
                    'publishedAt' => '2026-09-08T10:00:00Z',
                    'content' => 'Full article content.',
                ]],
            ], 200),
        ]);

        $articles = app(NewsProviderEngine::class)->fetch('test', 1, [
            'source' => 'newsapi', 'geography' => 'india', 'topic' => 'sports', 'from' => null, 'to' => null,
        ]);

        $this->assertCount(1, $articles);
        $this->assertSame('Example Sports', $articles[0]['source']);
        $this->assertSame('https://example.com/image.jpg', $articles[0]['image']);
        $this->assertSame('https://example.com/article', $articles[0]['url']);
        $this->assertSame('Full article content.', $articles[0]['content']);
    }

    public function test_google_hosted_image_is_not_saved_as_article_image(): void
    {
        config()->set('services.newsdata.key', '');
        config()->set('services.newsapi.key', '');
        Http::fake([
            'https://saurav.tech/NewsAPI/top-headlines/category/general/in.json' => Http::response([
                'status' => 'ok',
                'articles' => [[
                    'source' => ['id' => null, 'name' => 'Example'],
                    'title' => 'A real India news story',
                    'description' => 'A real description for the news story that should remain in the review queue.',
                    'url' => 'https://example.com/story',
                    'urlToImage' => 'https://lh3.googleusercontent.com/google-logo.jpg',
                    'publishedAt' => '2026-09-08T10:00:00Z',
                ]],
            ], 200),
        ]);

        $articles = app(NewsProviderEngine::class)->fetch('', 1, [
            'source' => 'newsapi', 'geography' => 'india', 'topic' => '', 'from' => null, 'to' => null,
        ]);

        $this->assertCount(1, $articles);
        $this->assertNull($articles[0]['image']);
        $this->assertSame('A real India news story', $articles[0]['title']);
    }
}
