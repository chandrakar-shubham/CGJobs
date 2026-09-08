<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\Job;
use App\Models\News;
use App\Services\AI\ContentEngine;
use Illuminate\Http\Request;

class AiContentEngineController extends Controller
{
    public function index()
    {
        return view('admin.ai-engine.index', [
            'contents' => AiContent::latest()->paginate(25),
            'setting' => AiProviderSetting::latest()->first(),
            'stats' => AiContent::selectRaw("count(*) as total, sum(status='pending') as pending, sum(status='generated') as generated, sum(status='failed') as failed, sum(status='published') as published")->first(),
            'newsIds' => News::latest('id')->limit(20)->pluck('id'),
            'jobIds' => Job::latest('id')->limit(20)->pluck('id'),
        ]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate(['provider'=>'required|in:gemini,groq','api_key'=>'nullable|string|max:500','model'=>'nullable|string|max:120','enabled'=>'nullable|boolean','daily_request_limit'=>'required|integer|min:1|max:100000','daily_token_limit'=>'required|integer|min:1|max:100000000','max_items_per_request'=>'required|integer|min:1|max:100','monthly_budget'=>'nullable|numeric|min:0','auto_publish_news'=>'nullable|boolean','auto_publish_jobs'=>'nullable|boolean']);
        $setting = AiProviderSetting::latest()->first() ?: new AiProviderSetting();
        if (!empty($data['api_key'])) $setting->api_key = $data['api_key'];
        unset($data['api_key']);
        $setting->fill($data);
        $setting->enabled = $request->boolean('enabled');
        $setting->auto_publish_news = $request->boolean('auto_publish_news');
        $setting->auto_publish_jobs = $request->boolean('auto_publish_jobs');
        $setting->save();
        return back()->with('success','AI provider settings saved securely.');
    }

    public function process(Request $request, ContentEngine $engine)
    {
        $data = $request->validate(['type'=>'required|in:news,job','ids'=>'required|array|min:1|max:100','ids.*'=>'integer']);
        $items = collect($data['ids'])->map(function ($id) use ($data, $engine) { if ($data['type']==='news') { $m=News::find($id); return $m?$engine->buildNews($m):null; } $m=Job::find($id); return $m?$engine->buildJob($m):null; })->filter()->values()->all();
        try { $saved=$engine->process($items); } catch (\Throwable $e) { return back()->withErrors(['ai'=>$e->getMessage()]); }
        return back()->with('success',count($saved).' item(s) generated in batch.');
    }

    public function preview(AiContent $aiContent)
    {
        abort_unless($aiContent->generated_content, 404);
        return view('admin.ai-engine.preview', ['content'=>$aiContent]);
    }

    public function retry(AiContent $aiContent, ContentEngine $engine)
    {
        try { $engine->process([['id'=>$aiContent->source_id,'source_type'=>$aiContent->source_type,'source'=>$aiContent->source_snapshot]]); }
        catch (\Throwable $e) { return back()->withErrors(['ai'=>$e->getMessage()]); }
        return back()->with('success','Content regenerated.');
    }

    public function publish(AiContent $aiContent)
    {
        if ($aiContent->source_type==='news') { $news=News::findOrFail($aiContent->source_id); $news->update(['status'=>'published','published_at'=>$news->published_at?:now()]); }
        else { $job=Job::findOrFail($aiContent->source_id); $job->update(['workflow_status'=>'published']); }
        $aiContent->update(['status'=>'published','published_at'=>now()]);
        return back()->with('success','Content published.');
    }
}
