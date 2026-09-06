<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSection;
use App\Models\Job;
use App\Models\StaticGk;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $lang = $this->language($request);
        $latest = $this->localizeJobs(Job::latest('id')->take(12)->get(), $lang);
        $jobs = $this->localizeJobs(Job::where(fn($q)=>$q->where('section','jobs')->orWhereNull('section'))->latest('id')->take(6)->get(), $lang);
        $currentAffairs = $this->localizeJobs(Job::whereIn('section',['current-affairs','current_affairs','news'])->latest('id')->take(6)->get(), $lang);
        $gk = $this->localizeGk(StaticGk::latest('id')->take(6)->get(), $lang);
        return view('web.home', compact('latest','jobs','currentAffairs','gk','lang'));
    }

    public function jobs(Request $request): View { return $this->listing('jobs',$request); }
    public function currentAffairs(Request $request): View { return $this->listing('current-affairs',$request); }
    public function staticGk(Request $request): View { return $this->listing('gk',$request); }

    public function listing(string $type, Request $request): View
    {
        $lang=$this->language($request);
        $map=['jobs'=>['title_hi'=>'नवीनतम सरकारी नौकरियां','title_en'=>'Latest Government Jobs','section_keys'=>['jobs']], 'current-affairs'=>['title_hi'=>'करेंट अफेयर्स व समाचार','title_en'=>'Current Affairs & News','section_keys'=>['current-affairs','current_affairs','news']], 'gk'=>['title_hi'=>'Static GK','title_en'=>'Static GK','section_keys'=>['gk','static-gk','static_gk']]];
        abort_unless(isset($map[$type]),404);
        $search=trim((string)$request->query('q','')); $category=trim((string)$request->query('category','')); $sort=(string)$request->query('sort','latest');
        if($type==='gk'){$query=StaticGk::query();$searchColumns=['title','title_en','hindi_title','question','question_en','answer','answer_en','category','category_en'];}
        else{$query=Job::query();$type==='jobs'?$query->where(fn($q)=>$q->where('section','jobs')->orWhereNull('section')):$query->whereIn('section',$map[$type]['section_keys']);$searchColumns=['title','title_en','summary','summary_en','detailed_content','detailed_content_en','category','category_en'];}
        if($search!=='')$query->where(function($q)use($searchColumns,$search){foreach($searchColumns as $c)$q->orWhere($c,'like','%'.$search.'%');});
        if($category!=='')$query->where(fn($q)=>$q->where('category',$category)->orWhere('category_en',$category)->orWhere('category_hindi',$category));
        if($sort==='oldest')$query->oldest('id');elseif($sort==='closing'&&$type==='jobs')$query->orderByRaw('CASE WHEN last_date IS NULL OR last_date = "" THEN 1 ELSE 0 END ASC')->orderBy('last_date')->orderByDesc('id');else$query->latest('id');
        if($type==='gk')$sectionCategories=AppSection::where('section_key','static_gk')->where('is_active',true)->with('categories')->get()->flatMap(fn($s)=>$s->categories)->pluck($lang==='en'?'name':'hindi_name');
        else$sectionCategories=AppSection::whereIn('section_key',$map[$type]['section_keys'])->where('is_active',true)->with('categories')->get()->flatMap(fn($s)=>$s->categories)->pluck($lang==='en'?'name':'hindi_name');
        $contentCategories=(clone $query)->reorder()->whereNotNull('category')->where('category','!=','')->select('category')->distinct()->orderBy('category')->pluck('category');
        $categories=$sectionCategories->merge($contentCategories)->filter()->unique()->values();
        $items=$query->paginate(18)->withQueryString();
        if($type==='gk')$items->setCollection($this->localizeGk($items->getCollection(),$lang));else$items->setCollection($this->localizeJobs($items->getCollection(),$lang));
        return view('web.listing',['type'=>$type,'title'=>$lang==='en'?$map[$type]['title_en']:$map[$type]['title_hi'],'items'=>$items,'categories'=>$categories,'search'=>$search,'selectedCategory'=>$category,'sort'=>$sort,'lang'=>$lang]);
    }

    public function job(Request $request,string $id): View
    {
        $lang=$this->language($request);$item=Job::where('custom_id',$id)->orWhere('id',$id)->firstOrFail();$related=Job::where('id','!=',$item->id)->where(fn($q)=>$q->where('category',$item->category)->orWhere('category_en',$item->category_en))->latest('id')->take(4)->get();$this->localizeJobs(collect([$item]),$lang);$related=$this->localizeJobs($related,$lang);return view('web.detail',['item'=>$item,'type'=>$this->jobType($item),'related'=>$related,'lang'=>$lang]);
    }

    public function gk(Request $request,string $id): View
    {
        $lang=$this->language($request);$item=StaticGk::where('custom_id',$id)->orWhere('id',$id)->firstOrFail();$related=StaticGk::where('id','!=',$item->id)->where(fn($q)=>$q->where('category',$item->category)->orWhere('category_en',$item->category_en))->latest('id')->take(4)->get();$this->localizeGk(collect([$item]),$lang);$related=$this->localizeGk($related,$lang);return view('web.gk-detail',compact('item','related','lang'));
    }

    public function sitemap(): Response
    {
        $base=rtrim(config('app.url'),'/');$urls=[['loc'=>$base.'/','changefreq'=>'daily','priority'=>'1.0'],['loc'=>$base.'/jobs','changefreq'=>'daily','priority'=>'0.9'],['loc'=>$base.'/current-affairs','changefreq'=>'daily','priority'=>'0.9'],['loc'=>$base.'/gk','changefreq'=>'weekly','priority'=>'0.8']];
        foreach(Job::select(['id','custom_id','section','updated_at'])->latest('id')->get() as $item){$type=$this->jobType($item);$urls[]=['loc'=>$base.'/'.($type==='current-affairs'?'current-affairs':'jobs').'/'.($item->custom_id?:$item->id),'lastmod'=>optional($item->updated_at)->toAtomString(),'priority'=>'0.7'];}
        foreach(StaticGk::select(['id','custom_id','updated_at'])->latest('id')->get() as $item)$urls[]=['loc'=>$base.'/gk/'.($item->custom_id?:$item->id),'lastmod'=>optional($item->updated_at)->toAtomString(),'priority'=>'0.6'];
        return response()->view('web.sitemap',compact('urls'))->header('Content-Type','application/xml; charset=UTF-8');
    }
    public function robots(): Response{$base=rtrim(config('app.url'),'/');return response("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api\nSitemap: {$base}/sitemap.xml\n",200,['Content-Type'=>'text/plain; charset=UTF-8']);}
    private function jobType(Job $job):string{return in_array($job->section,['current-affairs','current_affairs','news'],true)?'current-affairs':'jobs';}
    private function language(Request $request):string{return in_array($request->query('lang',session('locale','hi')),['hi','en'],true)?$request->query('lang',session('locale','hi')):'hi';}
    private function localizeJobs($items,string $lang){foreach($items as $item){$en=$lang==='en';$pick=fn($e,$h)=>$en?($e?:$h):($h?:$e);$item->title=$pick($item->title_en,$item->title);$item->summary=$pick($item->summary_en,$item->summary);$item->detailed_content=$pick($item->detailed_content_en,$item->detailed_content);$item->category=$pick($item->category_en,$item->category);$item->eligibility=$pick($item->eligibility_en,$item->eligibility);$item->selection_process=$pick($item->selection_process_en,$item->selection_process);$item->relative_time=$pick($item->relative_time_en,$item->relative_time);}return $items;}
    private function localizeGk($items,string $lang){foreach($items as $item){$en=$lang==='en';$pick=fn($e,$h)=>$en?($e?:$h):($h?:$e);$item->title=$pick($item->title_en,$item->hindi_title?:$item->title);$item->hindi_title=$item->title;$item->category=$pick($item->category_en,$item->category_hindi?:$item->category);$item->question=$pick($item->question_en,$item->question);$item->answer=$pick($item->answer_en,$item->answer);$item->detailed_notes=$pick($item->detailed_notes_en,$item->detailed_notes);$item->year_exam_reference=$pick($item->year_exam_reference_en,$item->year_exam_reference);$points=$en?$item->key_points_en:$item->key_points;$item->key_points=is_array($points)&&$points?$points:($item->key_points?:[]);}return $items;}
}
