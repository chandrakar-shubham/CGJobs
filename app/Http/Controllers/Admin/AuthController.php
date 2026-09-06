<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::get('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $expectedUser = env('ADMIN_USERNAME', 'admin');
        $expectedPass = env('ADMIN_PASSWORD', 'admin123');

        if ($username === $expectedUser && $password === $expectedPass) {
            Session::put('admin_logged_in', true);
            Session::put('admin_user', $username);
            return redirect()->route('admin.dashboard')->with('success', 'सफलतापूर्वक लॉगिन किया गया (Login successful!)');
        }

        return back()->withErrors(['error' => 'गलत यूजरनेम या पासवर्ड (Invalid Username or Password)'])->withInput();
    }

    public function logout()
    {
        Session::forget('admin_logged_in');
        Session::forget('admin_user');
        return redirect()->route('admin.login')->with('success', 'सफलतापूर्वक लॉगआउट किया गया (Logged out successfully)');
    }
}
