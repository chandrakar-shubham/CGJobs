<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class CacheController extends Controller
{
    public function clear()
    {
        try {
            Artisan::call('optimize:clear');

            return back()->with(
                'success',
                'Application cache cleared successfully. Laravel config, route, view, event and application caches have been cleared.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'cache' => 'Cache clear failed. Please check the server logs.',
            ]);
        }
    }
}
