<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $language = $request->query('lang') ?: $request->session()->get('locale', 'hi');
        $language = in_array($language, ['hi', 'en'], true) ? $language : 'hi';

        if ($request->query('lang')) {
            $request->session()->put('locale', $language);
        }

        app()->setLocale($language);
        view()->share('currentLanguage', $language);

        return $next($request);
    }
}
