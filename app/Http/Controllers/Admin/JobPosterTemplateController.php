<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JobPosterTemplateController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate(['template' => 'required|image|mimes:jpg,jpeg,png|max:5120']);
        $dir = public_path('assets');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        foreach (['job-poster-template.jpg','job-poster-template.jpeg','job-poster-template.png'] as $old) {
            if (is_file($dir.'/'.$old)) @unlink($dir.'/'.$old);
        }
        $request->file('template')->move($dir, 'job-poster-template.jpg');
        return back()->with('success', 'Poster template uploaded. All future job posters will use this fixed canvas.');
    }
}
