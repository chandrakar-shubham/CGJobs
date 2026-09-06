<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin.login')->withErrors(['error' => 'कृपया पहले एडमिन लॉगिन करें (Please login first)']);
        }

        return $next($request);
    }
}
