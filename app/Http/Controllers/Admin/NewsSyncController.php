<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NewsScraperService;
use Illuminate\Http\Request;

class NewsSyncController extends Controller
{
    public function index()
    {
        return view('admin.sync.index');
    }

    public function sync(Request $request, NewsScraperService $scraper)
    {
        $query = $request->input('query', 'Chhattisgarh recruitment jobs');
        $result = $scraper->syncRecruitmentNews($query);

        if ($result['imported'] > 0) {
            return back()->with('success', $result['message']);
        }

        return back()->with('info', $result['message']);
    }
}
