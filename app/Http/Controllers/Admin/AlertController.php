<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Job;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::orderBy('id', 'desc')->paginate(20);
        return view('admin.alerts.index', compact('alerts'));
    }

    public function create()
    {
        $recentJobs = Job::orderBy('id', 'desc')->take(10)->get();
        return view('admin.alerts.create', compact('recentJobs'));
    }

    public function store(Request $request, FirebaseNotificationService $fcmService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'required|string',
            'category' => 'required|string',
            'type' => 'required|string', // BREAKING, RECRUITMENT, EXAM_DATE, ADMIT_CARD, RESULT, DEADLINE
            'action_url' => 'nullable|url',
            'article_id' => 'nullable|string',
            'broadcast_now' => 'nullable|boolean',
        ]);

        $alert = Alert::create([
            'title' => $validated['title'],
            'short_description' => $validated['short_description'],
            'category' => $validated['category'],
            'type' => $validated['type'],
            'action_url' => $validated['action_url'],
            'article_id' => $validated['article_id'],
            'time' => 'हाल ही में',
            'is_broadcasted' => $request->has('broadcast_now'),
        ]);

        $msg = 'अलर्ट सफलतापूर्वक सहेजा गया (Alert saved successfully)';

        if ($request->has('broadcast_now')) {
            $fcmResult = $fcmService->broadcast(
                title: $alert->title,
                message: $alert->short_description,
                category: $alert->category,
                actionUrl: $alert->action_url,
                articleId: $alert->article_id
            );

            if ($fcmResult['success']) {
                $msg .= ' और सभी मोबाइल डिवाइसेस पर पुश नोटिफिकेशन प्रसारित किया गया!';
            }
        }

        return redirect()->route('admin.alerts.index')->with('success', $msg);
    }

    public function destroy(Alert $alert)
    {
        $alert->delete();
        return redirect()->route('admin.alerts.index')->with('success', 'अलर्ट हटा दिया गया (Alert deleted)');
    }
}
