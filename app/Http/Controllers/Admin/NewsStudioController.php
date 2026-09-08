<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\News;
use App\Services\AI\NewsContentEngine;
use App\Services\NewsIngestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsStudioController extends Controller
{
    public const CATEGORIES = ['Chhattisgarh','India','International','Government & Schemes','Economy & Banking','Environment & Ecology','Science & Technology','Defence','Polity & Governance','Awards & Appointments','Reports & Indexes','Important Days','Sports','CGPSC','CG Police','CG Education','CG Health','Current Affairs'];

    public function dashboard()
    {
        $stats = [
            'total' => News::count(),
            'fetched' => $this->reviewQuery()->count(),
            'processed' => AiContent::where('source_type','news')->where('status','generated')->count(),
            'published' => News::where('status','published')->count(),
            'archived' => News::where('status','archived')->count(),
            'failed' => AiContent::where('source_type','news')->where('status','failed')->count(),
            'web_published' => News::where('status','published')->where('published_web',true)->count(),
            'mobile_published' => News::where('status','published')->where('published_mobile',true)->count(),
            'today' => News::whereDate('created_at',today())->count(),
        ];
        $categoryStats = News::query()->select('category',DB::raw('count(*) as total'))->groupBy('category')->orderByDesc('total')->limit(8)->get();
        $sourceStats = News::query()->select('source',DB::raw('count(*) as total'))->whereNotNull('source')->where('source','!=','')->groupBy('source')->orderByDesc('total')->limit(6)->get();
        $recent = News::query()->orderByDesc('id')->limit(8)->get();
        $recentPublished = News::where('status','published')->orderByDesc('published_at')->orderByDesc('id')->limit(6)->get();
        return view('admin.news.dashboard',compact('stats','categoryStats','sourceStats','recent','recentPublished'));
    }

    public function index(Request $request)
    {
        $stage = in_array($request->input('stage','fetched'), ['fetched','processed','all'], true) ? $request->input('stage','fetched') : 'fetched';
        $query = News::query();
        if ($stage === 'processed') {
            $query->whereIn('status',['draft','review'])->whereExists(fn($q)=>$q->selectRaw('1')->from('ai_contents')->whereColumn('ai_contents.source_id','news.id')->where('ai_contents.source_type','news')->where('ai_contents.status','generated'));
        } elseif ($stage === 'all') {
            $query->whereIn('status',['published','archived']);
        } else {
            $query = $this->reviewQuery($query);
        }
        if($request->filled('search')){$term='%'.trim($request->search).'%';$query->where(fn($q)=>$q->where('title','like',$term)->orWhere('title_en','like',$term)->orWhere('summary','like',$term)->orWhere('summary_en','like',$term)->orWhere('source','like',$term));}
        if($request->filled('category'))$query->where('category',$request->category);
        if($request->filled('status'))$query->where('status',$request->status);
        $news=$query->orderByDesc('published_at')->orderByDesc('id')->paginate(24)->withQueryString();

        $batchIds = collect(session('news_ai_batch', []))->map(fn($id)=>(int)$id)->filter(fn($id)=>News::whereKey($id)->whereIn('status',['draft','review'])->exists())->values()->all();
        session(['news_ai_batch'=>$batchIds]);
        $aiByNews = $stage === 'processed'
            ? AiContent::where('source_type','news')->where('status','generated')->whereIn('source_id',$news->pluck('id'))->get()->keyBy('source_id')
            : collect();

        return view('admin.news.studio',['news'=>$news,'stage'=>$stage,'categories'=>self::CATEGORIES,'batchIds'=>$batchIds,'aiByNews'=>$aiByNews,'stats'=>[
            'fetched'=>$this->reviewQuery()->count(),
            'processed'=>AiContent::where('source_type','news')->where('status','generated')->count(),
            'published'=>News::where('status','published')->count(),
            'archived'=>News::where('status','archived')->count(),
            'failed'=>AiContent::where('source_type','news')->where('status','failed')->count(),
        ],'setting'=>AiProviderSetting::latest()->first()]);
    }

    private function reviewQuery($query = null)
    {
        $query = $query ?: News::query();
        return $query->whereIn('status',['draft','review'])->whereNotExists(fn($q)=>$q->selectRaw('1')->from('ai_contents')->whereColumn('ai_contents.source_id','news.id')->where('ai_contents.source_type','news')->whereIn('ai_contents.status',['generated','published']));
    }

    public function fetch(Request $request, NewsIngestionService $ingestion)
    {
        $data=$request->validate(['query'=>'nullable|string|max:300','topic'=>'nullable|string|max:80','geography'=>'required|in:chhattisgarh,india,world','source'=>'required|in:all,google_trending,google_news,newsdata,newsapi','from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from','limit'=>'required|integer|min:1|max:100']);
        try{$result=$ingestion->ingestAndProcess($data['query']??null,(int)$data['limit'],false,['topic'=>$data['topic']??null,'geography'=>$data['geography'],'source'=>$data['source'],'from'=>$data['from']??null,'to'=>$data['to']??null]);}catch(\Throwable $e){return back()->withErrors(['news'=>'News fetch failed: '.$e->getMessage()]);}
        return redirect()->route('admin.news.index',['stage'=>'fetched'])->with('success',"Fetched {$result['fetched']} source articles; added {$result['created']} new articles to the review queue. Existing articles were preserved.");
    }

    public function addToBatch(Request $request)
    {
        $data=$request->validate(['ids'=>'required|array|min:1|max:100','ids.*'=>'integer']);
        $existing=collect(session('news_ai_batch', []))->map(fn($id)=>(int)$id);
        $valid=News::whereIn('id',$data['ids'])->whereIn('status',['draft','review'])->pluck('id');
        $merged=$existing->merge($valid)->unique()->values()->all();
        session(['news_ai_batch'=>$merged]);
        return back()->with('success',count($valid).' article(s) added. AI Batch now contains '.count($merged).' article(s). Fetching more news will not remove this batch.');
    }

    public function clearBatch(){session()->forget('news_ai_batch');return back()->with('success','AI Batch cleared. News articles were not deleted.');}

    public function bulkDelete(Request $request)
    {
        $data=$request->validate(['ids'=>'required|array|min:1|max:100','ids.*'=>'integer']);
        $items=$this->reviewQuery(News::whereIn('id',$data['ids']))->get();
        $count=0;
        foreach($items as $news){$this->deleteNewsRecord($news);$count++;}
        return back()->with('success',$count.' selected review article(s) deleted.');
    }

    public function clearReviewQueue()
    {
        $items=$this->reviewQuery()->get();
        $count=0;
        foreach($items as $news){$this->deleteNewsRecord($news);$count++;}
        return back()->with('success',$count.' review article(s) cleared. Processed, published and archived news were not touched.');
    }

    private function deleteNewsRecord(News $news): void
    {
        AiContent::where('source_type','news')->where('source_id',$news->id)->delete();
        session(['news_ai_batch'=>collect(session('news_ai_batch',[]))->reject(fn($id)=>(int)$id===(int)$news->id)->values()->all()]);
        $news->delete();
    }

    public function batchProcess(Request $request, NewsContentEngine $engine)
    {
        $sessionIds=collect(session('news_ai_batch', []))->map(fn($id)=>(int)$id)->unique()->values();
        $postedIds=collect($request->input('ids', []))->map(fn($id)=>(int)$id)->filter()->unique()->values();
        $ids=$sessionIds->isNotEmpty() ? $sessionIds : $postedIds;
        if($ids->isEmpty())return back()->withErrors(['news'=>'AI Batch is empty. Select articles and use Add Selected to AI Batch first.']);
        if($ids->count()>100)return back()->withErrors(['news'=>'AI Batch can contain at most 100 articles per processing run.']);
        $setting=AiProviderSetting::where('enabled',true)->latest()->first();
        if(!$setting||!$setting->api_key)return back()->withErrors(['news'=>'AI is not configured. Save an enabled Gemini/Groq API key in AI Engine first.']);
        $items=News::whereIn('id',$ids)->whereIn('status',['draft','review'])->get()->map(fn($news)=>$engine->buildNews($news))->all();
        if(!$items)return back()->withErrors(['news'=>'No valid articles remain in the AI Batch.']);
        try{$engine->process($items);}catch(\Throwable $e){return back()->withErrors(['news'=>'AI processing failed: '.$e->getMessage()]);}

        // Keep failed items in the persistent batch. Previously the batch was cleared even when
        // every item failed validation/provider processing, which masked the real AI error as "0 processed".
        $successfulIds=AiContent::where('source_type','news')->whereIn('source_id',$ids->all())->whereIn('status',['generated','published'])->pluck('source_id')->map(fn($id)=>(int)$id);
        $remainingIds=$ids->diff($successfulIds)->values()->all();
        if($remainingIds)session(['news_ai_batch'=>$remainingIds]);else session()->forget('news_ai_batch');
        if($successfulIds->isEmpty()){
            $failure=AiContent::where('source_type','news')->whereIn('source_id',$ids->all())->where('status','failed')->orderByDesc('updated_at')->value('error_message');
            return back()->withErrors(['news'=>$failure ?: 'AI returned no successfully generated articles. The batch was kept for retry.']);
        }
        $message=count($successfulIds).' article(s) processed. Review website + mobile versions before publishing.';
        if($remainingIds)$message.=' '.count($remainingIds).' article(s) remain in the AI Batch because they did not generate successfully.';
        return redirect()->route('admin.news.index',['stage'=>'processed'])->with('success',$message);
    }

    public function publishSelected(Request $request)
    {
        $data=$request->validate(['ids'=>'required|array|min:1','ids.*'=>'integer']);$published=0;
        foreach(News::whereIn('id',$data['ids'])->get() as $news){$ai=AiContent::where('source_type','news')->where('source_id',$news->id)->where('status','generated')->latest()->first();if(!$ai)continue;$news->update(['status'=>'published','published_at'=>$news->published_at?:now(),'published_web'=>true,'published_mobile'=>true]);$ai->update(['status'=>'published','published_at'=>now()]);$published++;}
        return redirect()->route('admin.news.index',['stage'=>'all'])->with('success',$published.' article(s) published to Website + Mobile/App.');
    }

    public function channel(Request $request, News $news)
    {
        $data=$request->validate(['channel'=>'required|in:web,mobile','action'=>'required|in:publish,unpublish']);
        $column=$data['channel']==='web'?'published_web':'published_mobile';
        $news->update([$column=>$data['action']==='publish']);
        if($news->published_web||$news->published_mobile)$news->update(['status'=>'published','published_at'=>$news->published_at?:now()]);
        elseif($news->status==='published')$news->update(['status'=>'archived']);
        return back()->with('success',ucfirst($data['channel']).' '.($data['action']==='publish'?'published.':'unpublished.'));
    }

    public function archive(News $news){$news->update(['status'=>'archived','published_web'=>false,'published_mobile'=>false]);AiContent::where('source_type','news')->where('source_id',$news->id)->where('status','published')->update(['status'=>'generated','published_at'=>null]);return back()->with('success','News article archived.');}
    public function unpublish(News $news){$news->update(['status'=>'archived','published_web'=>false,'published_mobile'=>false]);AiContent::where('source_type','news')->where('source_id',$news->id)->where('status','published')->update(['status'=>'generated','published_at'=>null]);return back()->with('success','News article unpublished from Website + Mobile/App.');}
    public function view(News $news){$ai=AiContent::where('source_type','news')->where('source_id',$news->id)->latest()->first();return view('admin.news.view',compact('news','ai'));}
    public function destroy(News $news){$this->deleteNewsRecord($news);return back()->with('success','News article deleted.');}
}
