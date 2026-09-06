<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Category;
use App\Models\DeviceToken;
use App\Models\Job;

class DashboardController extends Controller
{
    public function index()
    {
        $totalJobs = Job::count();
        $breakingJobs = Job::where('is_breaking', true)->count();
        $totalAlerts = Alert::count();
        $totalDevices = DeviceToken::count();
        $totalCategories = Category::count();

        $recentJobs = Job::orderBy('id', 'desc')->take(5)->get();
        $recentAlerts = Alert::orderBy('id', 'desc')->take(5)->get();

        $categoryStats = Job::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalJobs',
            'breakingJobs',
            'totalAlerts',
            'totalDevices',
            'totalCategories',
            'recentJobs',
            'recentAlerts',
            'categoryStats'
        ));
    }
}
