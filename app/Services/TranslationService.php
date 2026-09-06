<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Translate English text to Hindi without a paid Google Cloud Translation key.
     *
     * This service uses the public Google Translate web endpoint used by the
     * open-source google-translate-api project. It is intentionally kept behind
     * one service so the rest of CGJobs does not depend on the provider details.
     *
     * Important: this endpoint is unofficial and can be rate-limited or changed
     * by Google at any time. Hindi is cached in our database after translation,
     * so visitors never make translation requests.
     */
    public function translate(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        try {
            $response = Http::timeout(25)
                ->retry(2, 500)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; CGJobs/1.0)',
                    'Accept' => 'application/json,text/plain,*/*',
                ])
                ->get('https://translate.google.com/translate_a/single', [
                    'client' => 't',
                    'sl' => 'en',
                    'tl' => 'hi',
                    'hl' => 'hi',
                    'dt' => ['at', 'bd', 'ex', 'ld', 'md', 'qca', 'rw', 'rm', 'ss', 't'],
                    'ie' => 'UTF-8',
                    'oe' => 'UTF-8',
                    'otf' => 1,
                    'ssel' => 0,
                    'tsel' => 0,
                    'kc' => 7,
                    'q' => $text,
                ]);

            if (!$response->successful()) {
                Log::warning('Free Google Translate request failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $body = $response->json();
            if (!is_array($body) || !isset($body[0]) || !is_array($body[0])) {
                Log::warning('Free Google Translate returned an unexpected response');
                return null;
            }

            $translated = '';
            foreach ($body[0] as $segment) {
                if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                    $translated .= $segment[0];
                }
            }

            return trim($translated) !== '' ? trim($translated) : null;
        } catch (\Throwable $e) {
            Log::warning('Free Google Translate exception: '.$e->getMessage());
            return null;
        }
    }

    /**
     * Translate multiple fields. Texts are sent one by one so the existing
     * controllers can keep their current field mapping and fallback behavior.
     */
    public function translateMany(array $texts): array
    {
        $result = [];

        foreach ($texts as $key => $text) {
            $result[$key] = $this->translate($text);
        }

        return $result;
    }
}
