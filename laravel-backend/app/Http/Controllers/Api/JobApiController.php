<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Job::query()->public();
        if ($request->filled('section')) $query->where('section', $request->section);
        if ($request->filled('category') && !in_array($request->category, ['सभी','All'], true)) {
            $cat = $request->category;
            $query->where(fn($q) => $q->where('category', $cat)->orWhere('category_en', $cat));
        }
        $this->applySearch($query, $request);
        $limit = min(max((int)$request->get('limit', 100), 1), 200);
        $jobs = $query->orderByDesc('id')->take($limit)->get();
        return response()->json(['success'=>true,'count'=>$jobs->count(),'news'=>$jobs->map(fn($job)=>$job->toApiArray($this->language($request)))->values()]);
    }

    public function jobs(Request $request): JsonResponse
    {
        $query = Job::query()->public()->where(fn($q) => $q->where('section','jobs')->orWhereNull('section'));
        if ($request->filled('category') && !in_array($request->category, ['सभी','All'], true)) {
            $cat = $request->category; $query->where(fn($q)=>$q->where('category',$cat)->orWhere('category_en',$cat));
        }
        if ($request->filled('job_category')) $query->where('job_category', $request->job_category);
        if ($request->filled('department')) $query->where('department', $request->department);
        $this->applySearch($query, $request);
        $limit = min(max((int)$request->get('limit',100),1),200);
        $jobs = $query->orderByDesc('id')->take($limit)->get();
        return response()->json(['success'=>true,'count'=>$jobs->count(),'jobs'=>$jobs->map(fn($job)=>$job->toApiArray($this->language($request)))->values()]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $job = Job::query()->public()->where(fn($q)=>$q->where('custom_id',$id)->orWhere('id',$id))->first();
        if (!$job) return response()->json(['success'=>false,'message'=>'Job notification not found'],404);
        return response()->json(['success'=>true,'item'=>$job->toApiArray($this->language($request))]);
    }

    public function categories(Request $request): JsonResponse
    {
        $query = Category::where('is_active',true)->orderBy('display_order');
        if ($request->filled('section')) $query->where('section_id',$request->section);
        $categories = $query->get();
        if ($categories->isEmpty()) return response()->json(['success'=>true,'categories'=>[]]);
        $lang = $this->language($request);
        return response()->json(['success'=>true,'categories'=>$categories->map(function($cat) use ($lang){
            $data=$cat->toApiArray();
            if ($lang==='en') { $data['name']=$data['name'] ?: ($cat->hindi_name ?: 'Category'); $data['hindiName']=$cat->hindi_name ?: $cat->name; }
            else { $data['name']=$cat->hindi_name ?: $cat->name; $data['hindiName']=$cat->hindi_name ?: $cat->name; }
            return $data;
        })->values()]);
    }

    private function applySearch($query, Request $request): void
    {
        if ($request->filled('query')) {
            $s='%'.$request->query('query').'%';
            $query->where(fn($q)=>$q->where('title','like',$s)->orWhere('title_en','like',$s)->orWhere('summary','like',$s)->orWhere('summary_en','like',$s)->orWhere('vacancies','like',$s)->orWhere('job_category','like',$s)->orWhere('department','like',$s));
        }
    }

    private function language(Request $request): string
    {
        return in_array($request->query('lang','hi'),['hi','en'],true) ? $request->query('lang','hi') : 'hi';
    }
}
