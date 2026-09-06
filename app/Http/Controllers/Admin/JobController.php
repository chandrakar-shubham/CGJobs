<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Services\FirebaseNotificationService;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    private const JOB_CATEGORIES = ['CGSSB','CGPSC','Central Govt','Contractual'];
    private const DEPARTMENTS = ['Education','Police','Revenue','PHE','PWD','Health','Women & Child Development','Forest','Agriculture','Panchayat','Transport','Other Departments'];

    public function index(Request $request)
    {
        $query=Job::query();
        if($request->filled('search')){$s='%'.$request->search.'%';$query->where(fn($q)=>$q->where('title','like',$s)->orWhere('title_en','like',$s)->orWhere('summary','like',$s)->orWhere('summary_en','like',$s)->orWhere('vacancies','like',$s));}
        if($request->filled('job_category')&&in_array($request->job_category,self::JOB_CATEGORIES,true))$query->where('job_category',$request->job_category);
        if($request->filled('department'))$query->where('department',$request->department);
        if($request->filled('category')&&$request->category!=='All')$query->where('category',$request->category);
        if($request->filled('section'))$query->where('section',$request->section);
        if($request->filled('post_type'))$query->where('post_type',$request->post_type);
        $jobs=$query->orderByDesc('id')->paginate(15);$categories=Category::orderBy('name')->get();
        return view('admin.jobs.index',compact('jobs','categories'))->with('jobCategories',self::JOB_CATEGORIES)->with('departments',self::DEPARTMENTS);
    }

    public function create(Request $request)
    {
        $defaultSection=$request->query('section','jobs');$categories=Category::orderBy('name')->get();
        return view('admin.jobs.create',compact('categories','defaultSection'))->with('jobCategories',self::JOB_CATEGORIES)->with('departments',self::DEPARTMENTS);
    }

    public function store(Request $request,FirebaseNotificationService $fcmService,TranslationService $translator)
    {
        $validated=$this->validatePost($request);$isJob=$request->input('section','jobs')==='jobs';
        $validated['section']=$request->input('section','jobs');$validated['post_type']=$request->input('post_type','job');$validated['is_breaking']=$request->has('is_breaking');$validated['is_new']=$request->has('is_new');$validated['published_at']=date('Y-m-d');$validated['relative_time']='हाल ही में';$validated['relative_time_en']='Recently';$validated['title_en']=$validated['title'];$validated['summary_en']=$validated['summary'];$validated['detailed_content_en']=$validated['detailed_content']??null;$validated['eligibility_en']=$validated['eligibility']??null;$validated['selection_process_en']=$validated['selection_process']??null;$validated['translation_status']='pending';
        if($isJob){$validated['category']=$validated['department'];$validated['category_en']=$validated['department'];}
        else{$validated['category_en']=$validated['category']??null;}
        $job=Job::create($validated);$this->translateJob($job,$translator);$this->sendPushIfRequested($request,$job,$fcmService);
        return redirect()->route('admin.jobs.index')->with('success',$isJob?'Job published. Main category and department taxonomy saved.':'Post published.');
    }

    public function edit(Job $job){$categories=Category::orderBy('name')->get();return view('admin.jobs.edit',compact('job','categories'))->with('jobCategories',self::JOB_CATEGORIES)->with('departments',self::DEPARTMENTS);}

    public function update(Request $request,Job $job,TranslationService $translator)
    {
        $validated=$this->validatePost($request);$isJob=$request->input('section',$job->section?:'jobs')==='jobs';$validated['section']=$request->input('section',$job->section?:'jobs');$validated['post_type']=$request->input('post_type',$job->post_type?:'job');$validated['is_breaking']=$request->has('is_breaking');$validated['is_new']=$request->has('is_new');$validated['title_en']=$validated['title'];$validated['summary_en']=$validated['summary'];$validated['detailed_content_en']=$validated['detailed_content']??null;$validated['eligibility_en']=$validated['eligibility']??null;$validated['selection_process_en']=$validated['selection_process']??null;$validated['relative_time_en']='Recently';$validated['translation_status']='pending';
        if($isJob){$validated['category']=$validated['department'];$validated['category_en']=$validated['department'];}else{$validated['category_en']=$validated['category']??$job->category_en;}
        $job->update($validated);$this->translateJob($job->fresh(),$translator);return redirect()->route('admin.jobs.index')->with('success',$isJob?'Job updated.':'Post updated.');
    }

    public function destroy(Job $job){$job->delete();return redirect()->route('admin.jobs.index')->with('success','अधिसूचना हटा दी गई (Job deleted)');}

    private function validatePost(Request $request): array
    {
        return $request->validate([
            'title'=>'required|string|max:255','summary'=>'required|string','detailed_content'=>'nullable|string',
            'category'=>'nullable|string','job_category'=>'required_if:section,jobs|in:CGSSB,CGPSC,Central Govt,Contractual','department'=>'required_if:section,jobs|string|max:120',
            'section'=>'nullable|string|in:jobs,news','post_type'=>'nullable|string','vacancies'=>'nullable|string','salary'=>'nullable|string','eligibility'=>'nullable|string','age_limit'=>'nullable|string','selection_process'=>'nullable|string','source'=>'nullable|string','source_url'=>'nullable|url','official_notification_url'=>'nullable|url','apply_url'=>'nullable|url','image_url'=>'nullable|url','application_start'=>'nullable|string','last_date'=>'nullable|string','exam_date'=>'nullable|string',
        ]);
    }

    private function sendPushIfRequested(Request $request,Job $job,FirebaseNotificationService $fcmService): void
    {
        if(!$request->has('broadcast_push'))return;
        $fcmService->broadcast(title:'नई भर्ती: '.($job->title?:$job->title_en),message:($job->vacancies?"[{$job->vacancies}] ":'').($job->summary?:$job->summary_en),category:$job->department?:$job->job_category,actionUrl:$job->apply_url?:$job->official_notification_url,articleId:(string)$job->id);
    }

    private function translateJob(Job $job,TranslationService $translator): void
    {
        try{$t=$translator->translateMany(['title'=>$job->title_en,'summary'=>$job->summary_en,'detailed'=>$job->detailed_content_en,'category'=>$job->category_en,'eligibility'=>$job->eligibility_en,'selection'=>$job->selection_process_en]);$ready=!empty($t['title']);$job->update(['title'=>$t['title']?:$job->title_en,'summary'=>$t['summary']?:$job->summary_en,'detailed_content'=>$t['detailed']?:$job->detailed_content_en,'category'=>$t['category']?:$job->category_en,'eligibility'=>$t['eligibility']?:$job->eligibility_en,'selection_process'=>$t['selection']?:$job->selection_process_en,'translation_status'=>$ready?'ready':'pending','translation_error'=>$ready?null:'Automatic Hindi translation temporarily unavailable.','translated_at'=>$ready?now():null]);}catch(\Throwable $e){$job->update(['translation_status'=>'failed','translation_error'=>'Automatic Hindi translation temporarily unavailable.']);}
    }
}
