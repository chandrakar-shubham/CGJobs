<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\News;
use App\Services\AI\ContentEngine;
use App\Services\NewsIngestionService;
use Illuminate\Http\Request;

class NewsStudioController extends Controller
{
    public const CATEGORIES = ['Chhattisgarh','India','International','Government & Schemes','Economy & Banking','Environment & Ecology','Science & Technology','Defence','Polity & Governance','Awards & Appointments','Reports & Indexes','Important Days','Sports','CGPSC','CG Police','CG Education','CG Health','Current Affairs'];

    public function index(Request $request)
    {
        $stage = in_array($request->input('stage','fetched'), ['fetched','processed','published'], true) ? $request->input('stage','fetched') : 'fetched';
        $query = News::query();
        if ($stage === 'processed') {
            $query->whereExists(fn($q)=>$q->selectRaw('1')->from('ai_contents')->whereColumn('ai_contents.source_id','news.id')->where('ai_contents.source_type','news')->whereIn('ai_contents.status',['generated','published']));
        } elseif ($stage === 'published') {
            $query->where('status','published');
        } else {
            $query->whereIn('status',['draft','review'])->whereNotExists(fn($q)=>$q->selectRaw('1')->from('ai_contents')->whereColumn('ai_contents.source_id','news.id')->where('ai_contents.source_type','news')->whereIn('ai_contents.status',['generated','published']));
        }
        if($request->filled('search')){$term='%'.trim($request->search).'%';$query->where(fn($q)=>$q->where('title','like',$term)->orWhere('title_en','like',$term)->orWhere('summary','like',$term)->orWhere('summary_en','like',$term)->orWhere('source','like',$term));}
        if($request->filled('category'))$query->where('category',$request->category);
        if($request->filled('status'))$query->where('status',$request->status);
        $news=$query->orderByDesc('published_at')->orderByDesc('id')->paginate(24)->withQueryString();
        return view('admin.news.studio',['news'=>$news,'stage'=>$stage,'categories'=>self::CATEGORIES,'stats'=>[
            'fetched'=>News::whereIn('status',['draft','review'])->whereNotExists(fn($q)=>$q->selectRaw('1')->from('ai_contents')->whereColumn('ai_contents.source_id','news.id')->where('ai_contents.source_type','news')->whereIn('ai_contents.status',['generated','published']))->count(),
            'processed'=>AiContent::where('source_type','news')->whereIn('status',['generated','published'])->count(),
            'published'=>News::where('status','published')->count(),
            'failed'=>AiContent::where('source_type','news')->where('status','failed')->count(),
        ],'setting'=>AiProviderSetting::latest()->first()]);
    }

    public function fetch(Request $request, NewsIngestionService $ingestion)
    {
        $data=$request->validate(['query'=>'nullable|string|max:300','topic'=>'nullable|string|max:80','geography'=>'required|in:chhattisgarh,india,world','source'=>'required|in:all,google_trending,google_news,newsdata,newsapi','from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from','limit'=>'required|integer|min:1|max:100']);
        try{$result=$ingestion->ingestAndProcess($data['query']??null,(int)$data['limit'],false,['topic'=>$data['topic']??null,'geography'=>$data['geography'],'source'=>$data['source'],'from'=>$data['from']??null,'to'=>$data['to']??null]);}catch(\Throwable $e){return back()->withErrors(['news'=>'News fetch failed: '.$e->getMessage()]);}
        return redirect()->route('admin.news.index',['stage'=>'fetched'])->with('success',"Fetched {$result['fetched']} articles; added {$result['created']} new articles to the review queue.");
    }

    public function batchProcess(Request $request, ContentEngine $engine)
    {
        $data=$request->validate(['ids'=>'required|array|min:1|max:100','ids.*'=>'integer']);
        $setting=AiProviderSetting::where('enabled',true)->latest()->first();
        if(!$setting||!$setting->api_key)return back()->withErrors(['news'=>'AI is not configured. Save an enabled Gemini/Groq API key in AI Engine first.']);
        $items=News::whereIn('id',$data['ids'])->get()->map(fn($news)=>$engine->buildNews($news))->all();
        if(!$items)return back()->withErrors(['news'=>'No valid news items were selected.']);
        try{$saved=$engine->process($items);}catch(\Throwable $e){return back()->withErrors(['news'=>$e->getMessage()]);}
        return redirect()->route('admin.news.index',['stage'=>'processed'])->with('success',count($saved).' selected article(s) processed. Review them before publishing.');
    }

    public function publishSelected(Request $request)
    {
        $data=$request->validate(['ids'=>'required|array|min:1|max:100','ids.*'=>'integer']);$published=0;
        foreach(News::whereIn('id',$data['ids'])->get() as $news){$ai=AiContent::where('source_type','news')->where('source_id',$news->id)->whereIn('status',['generated','published'])->latest()->first();if(!$ai)continue;$news->update(['status'=>'published','published_at'=>$news->published_at?:now()]);$ai->update(['status'=>'published','published_at'=>now()]);$published++;}
        return back()->with('success',$published.' article(s) published.');
    }

    public function view(News $news){$ai=AiContent::where('source_type','news')->where('source_id',$news->id)->latest()->first();return view('admin.news.view',compact('news','ai'));}
    public function destroy(News $news){AiContent::where('source_type','news')->where('source_id',$news->id)->delete();$news->delete();return back()->with('success','News article deleted.');}
}
