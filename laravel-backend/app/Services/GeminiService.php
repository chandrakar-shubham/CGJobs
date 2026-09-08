<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $model;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->model = config('services.gemini.model') ?: 'gemini-3.5-flash';
        // Priority: AppSetting (saved via Admin Panel UI) -> config/env
        $this->apiKey = AppSetting::get('gemini_api_key') 
            ?: config('services.gemini.key') 
            ?: env('GEMINI_API_KEY');
    }

    /**
     * Check if Gemini API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Process raw scraped news with Gemini 3.5 Flash for CGPSC & Vyapam exams
     *
     * @param string $title
     * @param string $rawContent
     * @param string $source
     * @return array
     */
    public function processExamNews(string $title, string $rawContent, string $source = 'DPRCG'): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'used_gemini' => false,
                'message' => 'Gemini API key is not configured',
            ];
        }

        $prompt = <<<PROMPT
You are an expert examiner and current affairs editor for Chhattisgarh state competitive exams (CGPSC, CG व्यापम / CGSSB, CG Police, Teacher Bharti) and national exams (SSC, UPSC).

Analyze the following raw news article:
Source: {$source}
Title: {$title}
Content: {$rawContent}

TASK:
1. Determine if this news is useful for competitive examinations (e.g., Chhattisgarh govt schemes, cabinet approvals, state budget, appointments, awards, bills, infrastructure, GI tags, CG history/culture facts, ISRO, national summits). If it is purely crime, political blame-games/rallies, local road mishaps, or gossip, set is_exam_relevant to false.
2. If is_exam_relevant is true, formulate:
   - inshorts_summary: Exactly 50 to 60 words in clean, formal Hindi. Inshorts capsule style answering What, Who, When, and Key Impact.
   - exam_takeaway: 3 to 4 clear, high-yield bullet points in Hindi with emoji headers:
     • नोडल विभाग / संस्था
     • मुख्य प्रावधान / पात्रता / आंकड़े
     • परीक्षा संदर्भ (CGPSC राज्य सेवा / व्यापम संयुक्त भर्ती)
   - category: One of ['शासकीय योजनाएं', 'छत्तीसगढ़ समसामयिकी', 'अर्थव्यवस्था व बजट', 'नवीनतम नियुक्तियां', 'खेल व पुरस्कार', 'प्रतियोगी परीक्षा समसामयिकी']
   - cleaned_detailed_content: A high-quality educational article in Hindi (200-300 words) with clear paragraphs explaining the background and significance for students.

Return ONLY a valid JSON object with these keys:
{
  "is_exam_relevant": true,
  "inshorts_summary": "50-60 words Hindi summary",
  "exam_takeaway": "📌 परीक्षा दृष्टि (CGPSC/व्यापम):\n• नोडल विभाग: ...\n• मुख्य बिंदु: ...\n• परीक्षा संदर्भ: ...",
  "category": "शासकीय योजनाएं",
  "cleaned_detailed_content": "Detailed Hindi educational article..."
}
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
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
                    // Remove markdown code fences if any
                    if (str_starts_with($cleanJson, '```json')) {
                        $cleanJson = substr($cleanJson, 7);
                    }
                    if (str_starts_with($cleanJson, '```')) {
                        $cleanJson = substr($cleanJson, 3);
                    }
                    if (str_ends_with($cleanJson, '```')) {
                        $cleanJson = substr($cleanJson, 0, -3);
                    }
                    $cleanJson = trim($cleanJson);

                    $parsed = json_decode($cleanJson, true);
                    if (is_array($parsed) && isset($parsed['is_exam_relevant'])) {
                        return [
                            'success' => true,
                            'used_gemini' => true,
                            'data' => $parsed,
                        ];
                    }
                }
            } else {
                Log::warning("Gemini API error ({$response->status()}): " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Gemini API call exception: " . $e->getMessage());
        }

        return [
            'success' => false,
            'used_gemini' => false,
            'message' => 'Gemini API processing failed or returned invalid response',
        ];
    }

    /**
     * Generate practice MCQs for a news item
     */
    public function generatePracticeQuestions(string $title, string $content): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $prompt = <<<PROMPT
Create 2 multiple choice questions (MCQs) in Hindi for CGPSC / CG व्यापम based on this current affairs item:
Title: {$title}
Content: {$content}

Format as JSON array:
[
  {
    "question": "प्रश्न हिंदी में?",
    "options": ["(A) विकल्प 1", "(B) विकल्प 2", "(C) विकल्प 3", "(D) विकल्प 4"],
    "correct_answer": "(A) विकल्प 1",
    "explanation": "संक्षिप्त व्याख्या हिंदी में।"
  }
]
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
            $response = Http::timeout(25)->post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
                $clean = trim(preg_replace('/^```json|```$/m', '', $text));
                $mcqs = json_decode($clean, true);
                return is_array($mcqs) ? $mcqs : [];
            }
        } catch (\Exception $e) {
            Log::warning("Gemini MCQ generation failed: " . $e->getMessage());
        }

        return [];
    }
}
