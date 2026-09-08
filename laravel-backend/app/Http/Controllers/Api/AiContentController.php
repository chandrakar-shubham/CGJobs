<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiContent;
use App\Models\News;
use App\Models\Job;
use App\Services\AI\ContentEngine;
use Illuminate\Http\Request;

class AiContentController extends Controller
{
    public function process(Request $request, ContentEngine $engine)
    {
        $data = $request->validate([
            'type' => 'required|in:news,job',
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'integer',
        ]);
        $items = collect($data['ids'])->map(function ($id) use ($data, $engine) {
            return $data['type'] === 'news'
                ? optional(News::find($id), fn($m) => $engine->buildNews($m))
                : optional(Job::find($id), fn($m) => $engine->buildJob($m));
        })->filter()->values()->all();
        return response()->json(['success'=>true,'processed'=>count($items),'items'=>$engine->process($items)]);
    }

    public function show(AiContent $aiContent)
    {
        return response()->json(['success'=>true,'content'=>$aiContent]);
    }
}
