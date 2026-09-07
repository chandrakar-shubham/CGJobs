<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Job;
use App\Models\JobDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobAdvancedController extends Controller
{
    private const CATEGORIES=['CGSSB','CGPSC','Central Govt','Contractual'];
    private const DEPARTMENTS=['Education','Police','Revenue','PHE','PWD','Health','Women & Child Development','Forest','Agriculture','Panchayat','Transport','Other Departments'];
    private function jobs(){return Job::query()->where(fn($q)=>$q->where('section','jobs')->orWhereNull('section'));}
    private function guard(Job $job):void{abort_unless($job->section==='jobs'||is_null($job->section),404);}
    public function republished(){
        $refs=Alert::where('type','RECRUITMENT_REPUBLISH')->whereNotNull('article_id')->pluck('article_id')->map(fn($v)=>(string)$v)->unique()->values();
        $jobs=$this->jobs()->where(fn($q)=>$q->whereIn('custom_id',$refs)->orWhereIn('id',$refs->filter(fn($v)=>ctype_digit($v))->map(fn($v)=>(int)$v)))->orderByDesc('published_at')->paginate(20);
        return view('admin.jobs.advanced',['mode'=>'republished','title'=>'Republished Jobs','jobs'=>$jobs]);
    }
    public function categories(){
        $categories=$this->jobs()->select('job_category',DB::raw('COUNT(*) total'))->groupBy('job_category')->get()->keyBy('job_category');
        $departments=$this->jobs()->select('department',DB::raw('COUNT(*) total'))->whereNotNull('department')->where('department','!=','')->groupBy('department')->orderByDesc('total')->get();
        return view('admin.jobs.advanced',['mode'=>'categories','title'=>'Categories & Departments','categories'=>self::CATEGORIES,'departments'=>$departments,'categoryCounts'=>$categories]);
    }
    public function notifications(){
        $alerts=Alert::whereIn('type',['NEW_JOB','JOB_PUBLISH','RECRUITMENT_REPUBLISH','BULK_JOB','JOB_DEADLINE_REMINDER','SCHEDULED_JOB'])->orderByDesc('created_at')->paginate(30);
        return view('admin.jobs.advanced',['mode'=>'notifications','title'=>'Notification Center','alerts'=>$alerts]);
    }
    public function notificationHistory(Job $job){$this->guard($job);$id=(string)($job->custom_id?:$job->id);$alerts=Alert::where('article_id',$id)->orderByDesc('created_at')->paginate(30);return view('admin.jobs.advanced',['mode'=>'notification-history','title'=>'Notification History','job'=>$job,'alerts'=>$alerts]);}
    public function versions(Job $job){$this->guard($job);$versions=$job->versions()->latest()->paginate(30);return view('admin.jobs.advanced',['mode'=>'versions','title'=>'Job Version History','job'=>$job,'versions'=>$versions]);}
    public function timeline(Job $job){
        $this->guard($job);$events=[];if($job->created_at)$events[]=['date'=>$job->created_at,'title'=>'Created','detail'=>'Job record created'];foreach($job->versions()->latest()->get() as $v)$events[]=['date'=>$v->created_at,'title'=>ucfirst($v->action),'detail'=>'Version '.$v->id.' saved'];if($job->scheduled_at)$events[]=['date'=>$job->scheduled_at,'title'=>'Scheduled','detail'=>'Scheduled publication'];if($job->published_at)$events[]=['date'=>$job->published_at,'title'=>'Published','detail'=>'Public publication date'];if($job->last_notification_at)$events[]=['date'=>$job->last_notification_at,'title'=>'Notification sent','detail'=>$job->notification_count.' notification(s) recorded'];usort($events,fn($a,$b)=>strtotime($b['date'])<=>strtotime($a['date']));return view('admin.jobs.advanced',['mode'=>'timeline','title'=>'Job Timeline','job'=>$job,'events'=>$events]);
    }
    public function documents(Job $job){$this->guard($job);return view('admin.jobs.advanced',['mode'=>'documents','title'=>'Official Documents','job'=>$job,'documents'=>$job->documents()->latest()->get()]);}
    public function storeDocument(Request $r,Job $job){$this->guard($job);$data=$r->validate(['title'=>'required|string|max:255','document_type'=>'required|string|max:40','url'=>'required|url']);$job->documents()->create($data);return back()->with('success','Official document added.');}
    public function destroyDocument(JobDocument $document){$document->delete();return back()->with('success','Document removed.');}
    public function seo(Job $job){$this->guard($job);return view('admin.jobs.advanced',['mode'=>'seo','title'=>'SEO Manager','job'=>$job]);}
    public function updateSeo(Request $r,Job $job){$this->guard($job);$data=$r->validate(['seo_title'=>'nullable|string|max:255','seo_description'=>'nullable|string|max:500','seo_keywords'=>'nullable|string|max:1000','canonical_url'=>'nullable|url']);$job->update($data);return back()->with('success','SEO settings saved.');}
    public function quality(){
        $jobs=$this->jobs()->latest('id')->paginate(30);$scores=[];foreach($jobs as $job){$checks=['title'=>filled($job->title),'summary'=>filled($job->summary),'category'=>in_array($job->job_category,self::CATEGORIES,true),'department'=>filled($job->department),'dates'=>filled($job->last_date),'apply'=>filled($job->apply_url),'notification'=>filled($job->official_notification_url),'eligibility'=>filled($job->eligibility)];$scores[$job->id]=round(count(array_filter($checks))/count($checks)*100);}return view('admin.jobs.advanced',['mode'=>'quality','title'=>'Job Quality Checker','jobs'=>$jobs,'scores'=>$scores]);
    }
    public function duplicates(){
        $rows=$this->jobs()->select('id','title','title_en','job_category','department','source','last_date')->whereNotNull('title')->get();$groups=$rows->groupBy(fn($j)=>preg_replace('/[^a-z0-9]+/','',strtolower((string)$j->title)))->filter(fn($g)=>$g->count()>1);return view('admin.jobs.advanced',['mode'=>'duplicates','title'=>'Duplicate Detection','groups'=>$groups]);
    }
    public function analytics(){
        $q=$this->jobs();$stats=['total'=>(clone $q)->count(),'published'=>(clone $q)->where(fn($x)=>$x->whereNull('workflow_status')->orWhere('workflow_status','published'))->count(),'views'=>(int)(clone $q)->sum('views_count'),'apply_clicks'=>(int)(clone $q)->sum('apply_clicks'),'notifications'=>(int)(clone $q)->sum('notification_count')];$byCategory=(clone $q)->select('job_category',DB::raw('COUNT(*) total'),DB::raw('SUM(views_count) views'),DB::raw('SUM(apply_clicks) clicks'))->groupBy('job_category')->orderByDesc('total')->get();$byMonth=(clone $q)->select(DB::raw("DATE_FORMAT(created_at,'%Y-%m') month"),DB::raw('COUNT(*) total'),DB::raw('SUM(views_count) views'),DB::raw('SUM(apply_clicks) clicks'))->groupBy('month')->orderBy('month','desc')->take(12)->get();return view('admin.jobs.advanced',['mode'=>'analytics','title'=>'Advanced Analytics','stats'=>$stats,'byCategory'=>$byCategory,'byMonth'=>$byMonth]);
    }
    public function importSync(){return redirect()->route('admin.job-sources.index')->with('info','Jobs Import / Sync is managed from the existing Sources center.');}
}