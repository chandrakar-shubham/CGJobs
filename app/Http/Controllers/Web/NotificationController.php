<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $lang = in_array($request->query('lang', session('language', 'hi')), ['hi', 'en'], true)
            ? $request->query('lang', session('language', 'hi'))
            : 'hi';

        return view('web.notifications', [
            'alerts' => Alert::latest('id')->paginate(20)->withQueryString(),
            'lang' => $lang,
        ]);
    }
}
