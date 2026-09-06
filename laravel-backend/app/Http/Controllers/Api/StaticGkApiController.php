<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaticGk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticGkApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StaticGk::query();
        if ($request->filled('category')) {
            $cat=$request->category;
            $query->where(fn($q)=>$q->where('category',$cat)->orWhere('category_hindi',$cat)->orWhere('category_en',$cat));
        }
        if ($request->filled('query')) {
            $s='%'.$request->query('query').'%';
            $query->where(fn($q)=>$q->where('title','like',$s)->orWhere('title_en','like',$s)->orWhere('hindi_title','like',$s)->orWhere('question','like',$s)->orWhere('question_en','like',$s)->orWhere('answer','like',$s)->orWhere('answer_en','like',$s));
        }
        $limit=min(max((int)$request->query('limit',100),1),200);
        $lang=in_array($request->query('lang','hi'),['hi','en'],true)?$request->query('lang','hi'):'hi';
        $items=$query->orderBy('display_order')->orderByDesc('id')->limit($limit)->get()->map(fn($item)=>$item->toApiArray($lang));
        return response()->json(['success'=>true,'count'=>$items->count(),'items'=>$items]);
    }
}
