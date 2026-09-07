<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AppSection;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\DeviceToken;
use App\Models\Job;
use App\Models\StaticGk;

class DashboardController extends Controller
{
    public function index()
    {
        $totalJobs = Job::where(function ($q) { $q->where('section', 'jobs')->orWhereNull('section'); })->count();
        $totalNews = Job::where('section', 'news')->count();
        $totalGk = StaticGk::count();
        $totalSections = AppSection::count();
        $breakingJobs = Job::where(function ($q) { $q->where('section', 'jobs')->orWhereNull('section'); })->where('is_breaking', true)->count();
        $totalAlerts = Alert::count();
        $totalDevices = DeviceToken::count();
        $totalCategories = Category::count();

        $tickerText = AppSetting::get('ticker_text', 'CGPSC राज्य सेवा परीक्षा 2026 प्रारंभिक परीक्षा की तिथि जारी | व्यापम शिक्षक पात्रता TET प्रवेश पत्र डाउनलोड करें');
        $tickerEnabled = AppSetting::get('ticker_enabled', '1') === '1';

        // The dashboard's published-jobs panel must never mix news records or
        // source names into the job taxonomy. Keep the existing view intact,
        // but feed it the canonical Main Category → Department value.
        $recentJobs = Job::query()
            ->where(function ($q) { $q->where('section', 'jobs')->orWhereNull('section'); })
            ->orderByDesc('id')
            ->take(5)
            ->get()
            ->each(function ($job) {
                $job->category = ($job->job_category ?: 'CGSSB').' → '.($job->department ?: 'Other Departments');
            });
        $recentAlerts = Alert::orderBy('id', 'desc')->take(5)->get();

        $categoryStats = Job::query()
            ->where(function ($q) { $q->where('section', 'jobs')->orWhereNull('section'); })
            ->selectRaw('job_category, count(*) as count')
            ->groupBy('job_category')
            ->pluck('count', 'job_category')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalJobs',
            'totalNews',
            'totalGk',
            'totalSections',
            'breakingJobs',
            'totalAlerts',
            'totalDevices',
            'totalCategories',
            'tickerText',
            'tickerEnabled',
            'recentJobs',
            'recentAlerts',
            'categoryStats'
        ));
    }
}
