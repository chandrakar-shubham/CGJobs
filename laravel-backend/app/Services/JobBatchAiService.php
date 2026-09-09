<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Job;
use App\Models\JobImport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobBatchAiService
{
    protected string $model;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->model = config('services.gemini.model') ?: 'gemini-3.8-flash';
        $this->apiKey = AppSetting::get('gemini_api_key')
            ?: config('services.gemini.key')
            ?: env('GEMINI_API_KEY');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Process multiple raw job items in ONE single batch API call to Gemini
     *
     * @param array $items Array of items: [['id' => 1, 'category' => '...', 'title' => '...', 'raw_text' => '...', 'source' => '...']]
     * @return array Result array with processed jobs and status
     */
    public function processBatch(array $items): array
    {
        if (empty($items)) {
            return ['success' => false, 'message' => 'No items provided for batch processing'];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Gemini API key is not configured. Please save it in Settings / Sync.',
                'fallback' => true,
            ];
        }

        // Prepare the batch payload for Gemini
        $batchPayload = [];
        foreach ($items as $item) {
            $batchPayload[] = [
                'id' => $item['id'] ?? null,
                'category' => $item['category'] ?? 'CGSSB',
                'title' => $item['title'] ?? '',
                'raw_content' => Str::limit($item['raw_content'] ?? ($item['raw_text'] ?? ''), 1200),
                'source' => $item['source_name'] ?? ($item['source'] ?? 'CG Govt Portal'),
            ];
        }

        $jsonInput = json_encode($batchPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
You are an expert government job portal editor and recruitment analyst for Chhattisgarh state exams (CGPSC, CGSSB/Vyapam, Contractual/District) and Central Government jobs.

I have provided an array of {$jsonInput} raw job items.
For EACH item in the array, analyze its title and content, and extract high-accuracy structured recruitment data.

STRICT INSTRUCTIONS FOR EACH ITEM:
1. "category": Must be one of ["CGPSC", "CGSSB", "CONTRACTUAL", "CENTRAL GOVT"].
2. "subcategory": Must be the hiring department, strictly one of:
   ["Education", "Health", "PWD", "CSEB", "Police", "Revenue", "Panchayat", "Agriculture", "Judiciary", "Banking", "General"].
3. "department": Full official name of the hiring department in Hindi & English (e.g. "स्कूल शिक्षा विभाग (School Education Department)").
4. "district": "All Chhattisgarh" or the specific district name if district-level recruitment.
5. "designation": Clean job title (e.g. "सहायक शिक्षक (Assistant Teacher)").
6. "vacancies": Total numeric vacancy count (integer, e.g. 242). If unknown or not mentioned, set to null.
7. "qualification": Clear, concise eligibility criteria in Hindi (e.g. "12वीं उत्तीर्ण + D.El.Ed / B.Ed + CG TET").
8. "age_limit": Age eligibility (e.g. "21 से 35 वर्ष (छत्तीसगढ़ निवासियों को नियमानुसार छूट)").
9. "salary": Pay scale or monthly stipend (e.g. "लेवल 6 (₹25,300 - ₹80,500)").
10. "application_start": Date string (YYYY-MM-DD) or null if not yet started/declared.
11. "last_date": Date string (YYYY-MM-DD) or null if not declared.
12. "exam_date": Date string (YYYY-MM-DD) or null.
13. "summary_60_words": EXACTLY 50 to 60 words in clean, formal Hindi for mobile app capsule. Answers What, Who, Eligibility, and Deadline.
14. "detailed_web_article_html": A comprehensive 400 to 600 words rich, SEO-friendly Hindi educational article formatted with clean HTML headings:
    - <h2> पद एवं भर्ती का संक्षिप्त परिचय</h2>
    - <h3>1. पदों का विवरण एवं रिक्तियां (Vacancy Breakdown)</h3>
    - <h3>2. शैक्षणिक योग्यता एवं पात्रता (Eligibility Criteria)</h3>
    - <h3>3. आयु सीमा एवं छूट (Age Limit)</h3>
    - <h3>4. चयन प्रक्रिया (Selection Process)</h3>
    - <h3>5. ऑनलाइन आवेदन कैसे करें (How to Apply Step-by-Step)</h3>
15. "selection_process": Short text summary of stages (e.g. "लिखित परीक्षा ➔ दस्तावेज सत्यापन ➔ मेरिट सूची").

Return ONLY a valid JSON array containing the analyzed objects matching the input IDs:
[
  {
    "id": 1,
    "category": "CGSSB",
    "subcategory": "Education",
    "department": "स्कूल शिक्षा विभाग (School Education)",
    "district": "All Chhattisgarh",
    "designation": "सहायक शिक्षक",
    "vacancies": 12489,
    "qualification": "12वीं + D.El.Ed / B.Ed + CG TET",
    "age_limit": "21 से 35 वर्ष",
    "salary": "Level 6 (₹25,300 - ₹80,500)",
    "application_start": "2026-02-10",
    "last_date": "2026-03-15",
    "exam_date": "2026-05-03",
    "summary_60_words": "...",
    "detailed_web_article_html": "<h2>...</h2>",
    "selection_process": "..."
  }
]
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'topP' => 0.95,
                        'responseMimeType' => 'application/json',
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($text) {
                    $cleanJson = trim($text);
                    if (str_starts_with($cleanJson, '```json')) {
                        $cleanJson = substr($cleanJson, 7);
                    }
                    if (str_starts_with($cleanJson, '```')) {
                        $cleanJson = substr($cleanJson, 3);
                    }
                    if (str_ends_with($cleanJson, '```')) {
                        $cleanJson = substr($cleanJson, 0, -3);
                    }

                    $parsedBatch = json_decode(trim($cleanJson), true);

                    if (is_array($parsedBatch)) {
                        return [
                            'success' => true,
                            'processed' => $parsedBatch,
                            'count' => count($parsedBatch),
                        ];
                    }
                }
            }

            Log::warning("Gemini Batch Job Processing response error: " . $response->body());
            return [
                'success' => false,
                'message' => 'Gemini API returned an invalid response or rate limit was reached.',
                'raw_response' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("Gemini Batch Job Processing exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Batch processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create or update a Job record from the Gemini processed result
     */
    public function createOrUpdateJobRecord(JobImport $import, array $aiData): Job
    {
        $category = in_array($aiData['category'] ?? '', ['CGPSC', 'CGSSB', 'CONTRACTUAL', 'CENTRAL GOVT'])
            ? $aiData['category']
            : ($import->job_category ?: 'CGSSB');

        $subcategory = $aiData['subcategory'] ?? ($import->department ?: 'Other Departments');
        $department = $aiData['department'] ?? $subcategory;

        // Strict Masking for public display source
        $publicSource = match($category) {
            'CGPSC'        => 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC)',
            'CGSSB'        => 'छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (CG Vyapam)',
            'CONTRACTUAL'  => 'शासकीय भर्ती (छत्तीसगढ़ शासन)',
            'CENTRAL GOVT' => 'भारत सरकार भर्ती (Central Government)',
            default        => 'शासकीय रोजगार सूचना'
        };

        $slugBase = Str::slug($aiData['designation'] ?? $import->title) ?: 'cg-job-' . time();
        $slug = $slugBase . '-' . ($import->id ?? rand(100, 999));

        $jobData = [
            'custom_id' => 'job-' . strtolower($category) . '-' . ($import->id ?? rand(1000, 9999)),
            'slug' => $slug,
            'title' => $import->title,
            'title_en' => $aiData['designation'] ?? $import->title,
            'summary' => $aiData['summary_60_words'] ?? $import->summary,
            'summary_en' => $aiData['summary_60_words'] ?? $import->summary,
            'detailed_content' => $aiData['detailed_web_article_html'] ?? $import->content,
            'detailed_content_en' => $aiData['detailed_web_article_html'] ?? $import->content,
            'category' => $department,
            'category_en' => $department,
            'job_category' => $category,
            'department' => $department,
            'section' => 'jobs',
            'post_type' => $import->raw_payload['post_type'] ?? 'job',
            'workflow_status' => 'draft',
            'source' => $publicSource,
            'source_url' => $import->external_url,
            'vacancies' => $aiData['vacancies'] ?? null,
            'salary' => $aiData['salary'] ?? null,
            'eligibility' => $aiData['qualification'] ?? null,
            'eligibility_en' => $aiData['qualification'] ?? null,
            'age_limit' => $aiData['age_limit'] ?? null,
            'selection_process' => $aiData['selection_process'] ?? null,
            'application_start' => $aiData['application_start'] ?? null,
            'last_date' => $aiData['last_date'] ?? null,
            'exam_date' => $aiData['exam_date'] ?? null,
            'official_notification_url' => $import->raw_payload['official_notification_url'] ?? $import->external_url,
            'apply_url' => $import->raw_payload['apply_url'] ?? $import->external_url,
            'image_url' => $import->image_url,
            'is_breaking' => false,
            'is_new' => true,
        ];

        // If import already has a job_id
        if ($import->job_id && $existing = Job::find($import->job_id)) {
            $existing->update($jobData);
            $job = $existing;
        } else {
            $job = Job::create($jobData);
            $import->update(['job_id' => $job->id]);
        }

        // Generate poster banner if image_url is missing
        if (empty($job->image_url)) {
            try {
                $posterService = app(JobPosterService::class);
                $svg = $posterService->render($job);
                // Save SVG banner to public storage
                $posterPath = 'job-banners/job-' . $job->id . '.svg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($posterPath, $svg);
                $job->update(['image_url' => asset('storage/' . $posterPath)]);
            } catch (\Throwable $e) {
                Log::warning("Poster generation error for job {$job->id}: " . $e->getMessage());
            }
        }

        return $job;
    }
}
