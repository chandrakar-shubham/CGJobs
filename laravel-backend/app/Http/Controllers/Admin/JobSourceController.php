<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobImport;
use App\Models\JobSource;
use App\Services\JobImportNormalizer;
use App\Services\JobPublishNotificationService;
use App\Services\JobSourceService;
use Illuminate\Http\Request;

class JobSourceController extends Controller
{
    private const MAIN_CATEGORIES = ['CGSSB', 'CGPSC', 'Central Govt', 'Contractual'];
    private const DEPARTMENTS = ['Education','Police','Revenue','PHE','PWD','Health','Women & Child Development','Forest','Agriculture','Panchayat','Transport','Other Departments'];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = JobImport::with(['source','job'])
            ->when($filters['q'], fn($q) => $q->where(fn($w) => $w->where('title','like','%'.$filters['q'].'%')->orWhere('external_url','like','%'.$filters['q'].'%')->orWhere('department','like','%'.$filters['q'].'%')))
            ->when($filters['source_id'], fn($q) => $q->where('job_source_id',$filters['source_id']))
            ->when($filters['job_category'], fn($q) => $q->where('job_category',$filters['job_category']))
            ->when($filters['department'], fn($q) => $q->where('department',$filters['department']))
            ->when($filters['from'], fn($q) => $q->where(fn($w) => $w->whereDate('published_at','>=',$filters['from'])->orWhere(fn($x) => $x->whereNull('published_at')->whereDate('created_at','>=',$filters['from']))))
            ->when($filters['to'], fn($q) => $q->where(fn($w) => $w->whereDate('published_at','<=',$filters['to'])->orWhere(fn($x) => $x->whereNull('published_at')->whereDate('created_at','<=',$filters['to']))));

        $this->applySort($query, $filters['sort']);
        $pending = (clone $query)->where('status','pending')->paginate(25,['*'],'pending_page')->withQueryString();
        $published = (clone $query)->where('status','published')->paginate(25,['*'],'published_page')->withQueryString();

        return view('admin.job-sources.index', [
            'sources' => JobSource::latest()->get(),
            'pending' => $pending,
            'published' => $published,
            'filters' => $filters,
            'mainCategories' => self::MAIN_CATEGORIES,
            'departments' => self::DEPARTMENTS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:120','base_url'=>'required|url|max:500','fetch_url'=>'required|url|max:1000',
            'source_type'=>'required|in:html,rss,blogger,wordpress,json_api,rest_api','frequency_minutes'=>'required|integer|min:15|max:43200',
            'publish_mode'=>'required|in:approval,auto','default_category'=>'required|in:CGSSB,CGPSC,Central Govt,Contractual',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['notify_on_publish'] = $request->boolean('notify_on_publish');
        JobSource::create($data);
        return back()->with('success','Job source added successfully.');
    }

    public function update(Request $request, JobSource $jobSource)
    {
        $data = $request->validate([
            'name'=>'required|string|max:120','base_url'=>'required|url|max:500','fetch_url'=>'required|url|max:1000',
            'source_type'=>'required|in:html,rss,blogger,wordpress,json_api,rest_api','frequency_minutes'=>'required|integer|min:15|max:43200',
            'publish_mode'=>'required|in:approval,auto','default_category'=>'required|in:CGSSB,CGPSC,Central Govt,Contractual',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['notify_on_publish'] = $request->boolean('notify_on_publish');
        $jobSource->update($data);
        return back()->with('success','Job source updated.');
    }

    public function toggleNotify(JobSource $jobSource)
    {
        $jobSource->update(['notify_on_publish' => ! $jobSource->notify_on_publish]);
        return back()->with('success', 'Publish notification '.($jobSource->notify_on_publish ? 'enabled' : 'disabled').' for '.$jobSource->name.'.');
    }

    public function destroy(JobSource $jobSource){$jobSource->delete();return back()->with('success','Job source removed.');}

    public function sync(Request $request,JobSource $jobSource,JobSourceService $service,JobImportNormalizer $normalizer){try{$from=$request->filled('sync_from')?$request->date('sync_from'):null;$to=$request->filled('sync_to')?$request->date('sync_to'):null;$result=$service->sync($jobSource,$from,$to,true);JobImport::where('job_source_id',$jobSource->id)->where('status','pending')->latest('id')->limit(max(1,(int)$result['imported']))->get()->each(fn($import)=>$normalizer->normalize($import));return back()->with('success',"Sync complete: {$result['fetched']} fetched, {$result['imported']} imported, {$result['skipped']} skipped. Titles, categories and source media normalized.");}catch(\Throwable $e){report($e);return back()->withErrors(['sync'=>'Sync failed: '.$e->getMessage()]);}}

    public function refreshPending(JobSource $jobSource,JobSourceService $service,JobImportNormalizer $normalizer){try{$removed=JobImport::where('job_source_id',$jobSource->id)->where('status','pending')->delete();$result=$service->sync($jobSource,null,null,true);JobImport::where('job_source_id',$jobSource->id)->where('status','pending')->latest('id')->get()->each(fn($import)=>$normalizer->normalize($import));return back()->with('success',"Re-fetched source after replacing {$removed} old pending imports: {$result['fetched']} fetched, {$result['imported']} imported, {$result['skipped']} skipped.");}catch(\Throwable $e){report($e);return back()->withErrors(['sync'=>'Refresh failed: '.$e->getMessage()]);}}

    public function editImport(JobImport $jobImport){$jobImport->load(['source','job']);return view('admin.job-sources.edit-import',['import'=>$jobImport,'mainCategories'=>self::MAIN_CATEGORIES,'departments'=>self::DEPARTMENTS]);}

    public function updateImport(Request $request,JobImport $jobImport){$data=$request->validate(['title'=>'required|string|max:250','summary'=>'nullable|string|max:2000','content'=>'nullable|string','job_category'=>'required|in:CGSSB,CGPSC,Central Govt,Contractual','department'=>'required|string|max:120','external_url'=>'required|url|max:2000','apply_url'=>'nullable|url|max:2000','notification_url'=>'nullable|url|max:2000','image_url'=>'nullable|url|max:2000','published_at'=>'nullable|date']);$payload=is_array($jobImport->raw_payload)?$jobImport->raw_payload:[];$facts=is_array($payload['facts']??null)?$payload['facts']:[];$facts['apply_url']=$data['apply_url']??null;$facts['notification_url']=$data['notification_url']??null;$payload['facts']=$facts;$payload['edited_in_admin']=true;$payload['edited_at']=now()->toIso8601String();$jobImport->update(['title'=>$data['title'],'summary'=>$data['summary']??null,'content'=>$data['content']??null,'category'=>$data['department'],'job_category'=>$data['job_category'],'department'=>$data['department'],'published_by'=>$data['job_category'],'external_url'=>$data['external_url'],'image_url'=>$data['image_url']??null,'published_at'=>$data['published_at']??null,'raw_payload'=>$payload]);if($jobImport->status==='published'&&$jobImport->job)$this->syncPublishedJob($jobImport->fresh('job'));return redirect()->route('admin.job-sources.index',$request->only(['q','source_id','job_category','department','from','to','sort']))->with('success','Imported job data updated successfully.');}

    public function approve(JobImport $jobImport,JobSourceService $service,JobImportNormalizer $normalizer,JobPublishNotificationService $notifier){$normalizer->normalize($jobImport);$job=$service->publish($jobImport->fresh());$job->update(['published_by'=>$job->published_by?:$job->job_category?:$jobImport->job_category?:'CGSSB']);$notifier->notify($jobImport->fresh('source'),$job->fresh());return back()->with('success','Approved and published. Website updated and notification workflow completed. View webpage: '.route('job.show',$job->custom_id?:$job->id));}

    public function approveAll(Request $request,JobSourceService $service,JobImportNormalizer $normalizer,JobPublishNotificationService $notifier){$filters=$this->filters($request);$query=JobImport::where('status','pending')->when($filters['q'],fn($q)=>$q->where(fn($w)=>$w->where('title','like','%'.$filters['q'].'%')->orWhere('external_url','like','%'.$filters['q'].'%')))->when($filters['source_id'],fn($q)=>$q->where('job_source_id',$filters['source_id']))->when($filters['job_category'],fn($q)=>$q->where('job_category',$filters['job_category']))->when($filters['department'],fn($q)=>$q->where('department',$filters['department']))->when($filters['from'],fn($q)=>$q->whereDate('published_at','>=',$filters['from']))->when($filters['to'],fn($q)=>$q->whereDate('published_at','<=',$filters['to']));$this->applySort($query,$filters['sort']);$count=0;$query->chunkById(100,function($imports)use($service,$normalizer,$notifier,&$count){foreach($imports as $import){$normalizer->normalize($import);$job=$service->publish($import->fresh());$notifier->notify($import->fresh('source'),$job->fresh());$count++;}});return back()->with('success',"{$count} imported jobs approved and published.");}

    public function reject(JobImport $jobImport){$jobImport->update(['status'=>'rejected','processed_at'=>now()]);return back()->with('success','Imported job rejected.');}

    private function filters(Request $request):array{return['q'=>trim((string)$request->query('q','')),'source_id'=>$request->query('source_id'),'job_category'=>$request->query('job_category'),'department'=>$request->query('department'),'from'=>$request->query('from'),'to'=>$request->query('to'),'sort'=>$request->query('sort','source_newest')];}

    private function applySort($query,string $sort):void{switch($sort){case 'source_oldest':$query->orderByRaw('published_at IS NULL, published_at ASC')->orderBy('id','asc');break;case 'category':$query->orderBy('job_category')->orderBy('published_at','desc');break;case 'department':$query->orderBy('department')->orderBy('published_at','desc');break;case 'fetched_newest':$query->orderBy('fetched_at','desc')->orderBy('id','desc');break;case 'title':$query->orderBy('title')->orderBy('id','desc');break;default:$query->orderByRaw('published_at IS NULL, published_at DESC')->orderBy('id','desc');}}

    private function syncPublishedJob(JobImport $import):void{$job=$import->job;$facts=is_array($import->raw_payload['facts']??null)?$import->raw_payload['facts']:[];$job->update(['title'=>$import->title,'title_en'=>$import->title,'summary'=>$import->summary,'summary_en'=>$import->summary,'detailed_content'=>$import->content?:$import->summary,'detailed_content_en'=>$import->content?:$import->summary,'category'=>$import->department,'category_en'=>$import->department,'job_category'=>$import->job_category,'department'=>$import->department,'published_by'=>$import->job_category?:'CGSSB','source_url'=>$import->external_url,'image_url'=>$import->image_url,'image_urls'=>$import->image_urls,'published_at'=>optional($import->published_at)->format('Y-m-d')?:$job->published_at,'apply_url'=>array_key_exists('apply_url',$facts)?$facts['apply_url']:$job->apply_url,'official_notification_url'=>array_key_exists('notification_url',$facts)?$facts['notification_url']:$job->official_notification_url]);}
}
