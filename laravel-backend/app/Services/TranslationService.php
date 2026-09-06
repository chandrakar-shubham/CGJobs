<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Translate English text to Hindi using Google Cloud Translation API v2.
     * If no API key is configured, the caller can keep the English fallback.
     */
    public function translate(?string $text): ?string
    {
        if ($text === null || trim($text) === '') return $text;

        $key = config('services.google_translate.key') ?: env('GOOGLE_TRANSLATE_API_KEY');
        if (!$key) return null;

        try {
            $response = Http::timeout(20)->retry(2, 300)->post(
                'https://translation.googleapis.com/language/translate/v2',
                [
                    'key' => $key,
                    'q' => $text,
                    'source' => 'en',
                    'target' => 'hi',
                    'format' => 'text',
                ]
            );

            if ($response->successful()) {
                return data_get($response->json(), 'data.translations.0.translatedText');
            }

            Log::warning('Google Translation failed', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::warning('Google Translation exception: '.$e->getMessage());
        }

        return null;
    }

    public function translateMany(array $texts): array
    {
        $result = [];
        foreach ($texts as $key => $text) {
            $result[$key] = $this->translate($text);
        }
        return $result;
    }
}
