<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\Job;
use App\Models\News;
use App\Services\AI\ContentEngine;
use App\Services\AI\NewsContentEngine;
use App\Services\NewsIngestionService;
use Illuminate\Http\Request;

class AiContentEngineController extends Controller
{
    public function index()
    {
        $setting = AiProviderSetting::latest()->first();
        $batchIds = collect(session('news_ai_batch', []))->map(fn ($id) => (int) $id)->filter()->values();
        $batchNews = News::whereIn('id', $batchIds)->whereIn('status', ['draft', 'review'])->get();
        session(['news_ai_batch' => $batchNews->pluck('id')->map(fn ($id) => (int) $id)->all()]);

        return view('admin.ai-engine.index', [
            'contents' => AiContent::latest()->paginate(25),
            'setting' => $setting,
            'stats' => AiContent::selectRaw("count(*) as total, sum(status='pending') as pending, sum(status='generated') as generated, sum(status='failed') as failed, sum(status='published') as published")->first(),
            'newsIds' => $batchNews->pluck('id'),
            'jobIds' => Job::latest('id')->limit(20)->pluck('id'),
            'batchNews' => $batchNews,
            'batchCount' => $batchNews->count(),
            'failedNews' => AiContent::where('source_type', 'news')->where('status', 'failed')->latest()->limit(10)->get(),
        ]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'provider'=>'required|in:gemini,groq', 'api_key'=>'nullable|string|max:500', 'model'=>'nullable|string|max:120',
            'fallback_provider'=>'nullable|in:gemini,groq', 'fallback_api_key'=>'nullable|string|max:500', 'fallback_model'=>'nullable|string|max:120',
            'enabled'=>'nullable|boolean', 'daily_request_limit'=>'required|integer|min:1|max:100000', 'daily_token_limit'=>'required|integer|min:1|max:100000000',
            'max_items_per_request'=>'required|integer|min:1|max:100', 'monthly_budget'=>'nullable|numeric|min:0', 'auto_publish_news'=>'nullable|boolean', 'auto_publish_jobs'=>'nullable|boolean',
        ]);
        $setting = AiProviderSetting::latest()->first() ?: new AiProviderSetting();
        if (!empty($data['api_key'])) $setting->api_key = $data['api_key'];
        if (!empty($data['fallback_api_key'])) $setting->fallback_api_key = $data['fallback_api_key'];
        unset($data['api_key'], $data['fallback_api_key']);
        $setting->fill($data);
        $setting->enabled = $request->boolean('enabled');
        $setting->auto_publish_news = $request->boolean('auto_publish_news');
        $setting->auto_publish_jobs = $request->boolean('auto_publish_jobs');
        if ($setting->provider === 'gemini' && (!$setting->model || $setting->model === 'gemini-2.5-flash')) $setting->model = 'gemini-3.8-flash';
        $setting->save();
        return back()->with('success', 'AI provider settings saved securely.');
    }

    public function ingestNews(Request $request, NewsIngestionService $ingestion)
    {
        $data = $request->validate(['query'=>'nullable|string|max:300','limit'=>'nullable|integer|min:1|max:100']);
        try {
            // Fetch only. Selection and processing stay in the same persistent News Studio batch.
            $r = $ingestion->ingestAndProcess($data['query'] ?? null, (int) ($data['limit'] ?? 20), false);
        } catch (\Throwable $e) {
            return back()->withErrors(['ai' => 'News fetch failed: ' . $e->getMessage()]);
        }
        return redirect()->route('admin.news.index', ['stage'=>'fetched'])->with('success', "Fetched {$r['fetched']} articles and added {$r['created']} new articles to News Studio. Select them there and Add to AI Batch.");
    }

    public function process(Request $request, ContentEngine $engine, NewsContentEngine $newsEngine)
    {
        $data = $request->validate(['type'=>'required|in:news,job','ids'=>'required|array|min:1|max:100','ids.*'=>'integer']);
        $setting = AiProviderSetting::where('enabled', true)->latest()->first();
        if (!$setting || !$setting->api_key) return back()->withErrors(['ai'=>'AI is disabled or not configured. Save an enabled provider/API key first.']);

        if ($data['type'] === 'news') {
            $sessionIds = collect(session('news_ai_batch', []))->map(fn ($id) => (int) $id)->unique()->values();
            $ids = $sessionIds->isNotEmpty() ? $sessionIds : collect($data['ids'])->map(fn ($id) => (int) $id)->unique()->values();
            $items = News::whereIn('id', $ids)->whereIn('status', ['draft','review'])->get()->map(fn ($news) => $newsEngine->buildNews($news))->all();
            if (!$items) return back()->withErrors(['ai'=>'No valid News records remain in the current AI Batch.']);
            try { $saved = $newsEngine->process($items); } catch (\Throwable $e) { return back()->withErrors(['ai'=>$e->getMessage()]); }
            session()->forget('news_ai_batch');
            return redirect()->route('admin.news.index', ['stage'=>'processed'])->with('success', count($saved) . ' News article(s) generated. Review Mobile + Website content before publishing.');
        }

        $items = collect($data['ids'])->map(function ($id) use ($engine) {
            $job = Job::find($id);
            return $job ? $engine->buildJob($job) : null;
        })->filter()->values()->all();
        if (!$items) return back()->withErrors(['ai'=>'No valid Job records were selected for processing.']);
        try { $saved = $engine->process($items); } catch (\Throwable $e) { return back()->withErrors(['ai'=>$e->getMessage()]); }
        return back()->with('success', count($saved) . ' Job item(s) generated.');
    }

    public function preview(AiContent $aiContent)
    {
        abort_unless($aiContent->generated_content, 404);
        return view('admin.ai-engine.preview', ['content'=>$aiContent]);
    }

    public function retry(AiContent $aiContent, ContentEngine $engine, NewsContentEngine $newsEngine)
    {
        try {
            if ($aiContent->source_type === 'news') {
                $news = News::findOrFail($aiContent->source_id);
                $newsEngine->process([$newsEngine->buildNews($news)]);
            } else {
                $job = Job::findOrFail($aiContent->source_id);
                $engine->process([$engine->buildJob($job)]);
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['ai'=>$e->getMessage()]);
        }
        return back()->with('success', 'Content regenerated.');
    }

    public function publish(AiContent $aiContent)
    {
        if ($aiContent->source_type === 'news') {
            $news = News::findOrFail($aiContent->source_id);
            $news->update(['status'=>'published','published_at'=>$news->published_at ?: now(),'published_web'=>true,'published_mobile'=>true]);
        } else {
            $job = Job::findOrFail($aiContent->source_id);
            $job->update(['workflow_status'=>'published']);
        }
        $aiContent->update(['status'=>'published','published_at'=>now()]);
        return back()->with('success', 'Content published.');
    }
}
