<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\GeminiService;
use App\Services\NewsScraperService;
use Illuminate\Http\Request;

class NewsSyncController extends Controller
{
    public function index(GeminiService $gemini)
    {
        $geminiConfigured = $gemini->isConfigured();
        $geminiModel = $gemini->getModel();
        $geminiKey = $gemini->getApiKey();
        $maskedKey = $geminiKey ? substr($geminiKey, 0, 6) . '...' . substr($geminiKey, -4) : null;

        return view('admin.sync.index', compact('geminiConfigured', 'geminiModel', 'maskedKey'));
    }

    /**
     * Save Gemini API Key directly from admin sync page
     */
    public function saveGeminiKey(Request $request)
    {
        $request->validate([
            'gemini_api_key' => 'nullable|string|max:255',
        ]);

        $key = trim($request->input('gemini_api_key', ''));
        if (!empty($key)) {
            AppSetting::set('gemini_api_key', $key, 'ai', 'Google Gemini API Key for Auto-Summaries and Exam Takeaways');
            return back()->with('success', 'Gemini API Key सफलतापूर्वक सहेजी गई! अब करंट अफेयर्स स्वतः Gemini 3.5 Flash द्वारा प्रोसेस होंगे।');
        } else {
            AppSetting::where('key', 'gemini_api_key')->delete();
            return back()->with('info', 'Gemini API Key हटा दी गई है। स्क्रैपर नियम-आधारित मोड में चलेगा।');
        }
    }

    /**
     * Live test/preview of Gemini processing on raw text
     */
    public function previewGemini(Request $request, GeminiService $gemini)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'source' => 'nullable|string|max:100',
        ]);

        if (!$gemini->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'कृपया पहले Gemini API Key दर्ज करें।',
            ], 422);
        }

        $result = $gemini->processExamNews(
            $request->input('title'),
            $request->input('content'),
            $request->input('source', 'दैनिक समाचार')
        );

        return response()->json($result);
    }

    /**
     * Run exam-oriented news scraper (DPRCG, PIB, Bhaskar, NewsData)
     */
    public function sync(Request $request, NewsScraperService $scraper)
    {
        $type = $request->input('sync_type', 'exam_news');

        if ($type === 'exam_news') {
            $result = $scraper->syncExamCurrentAffairs($request->input('query'));
        } else {
            // fallback
            $result = $scraper->syncExamCurrentAffairs($request->input('query'));
        }

        if ($result['imported'] > 0) {
            return back()->with('success', $result['message']);
        }

        return back()->with('info', $result['message']);
    }
}
