<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    private const MAX_CHUNK_LENGTH = 1800;

    /**
     * Translate English text to Hindi without a paid Google Cloud key.
     * Uses the public Google Translate web endpoint used by the open-source
     * google-translate-api project. Translations are cached in our database.
     *
     * This endpoint is unofficial and may be rate-limited or changed by Google.
     */
    public function translate(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        $text = trim($text);
        $chunks = $this->splitText($text);
        $translated = [];

        foreach ($chunks as $chunk) {
            $result = $this->translateChunk($chunk);
            if ($result === null) {
                return null;
            }
            $translated[] = $result;
        }

        return trim(implode("\n\n", $translated));
    }

    /**
     * Translate multiple fields. Empty fields are skipped. Each field is
     * chunked automatically so long job/news content does not exceed URL limits.
     */
    public function translateMany(array $texts): array
    {
        $result = [];

        foreach ($texts as $key => $text) {
            $result[$key] = $this->translate($text);
        }

        return $result;
    }

    private function translateChunk(string $text): ?string
    {
        try {
            $response = Http::timeout(25)
                ->retry(3, 750)
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
                    'length' => strlen($text),
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
     * Keep paragraphs together where possible and split oversized paragraphs
     * on sentence/word boundaries. This prevents long articles from producing
     * oversized Google Translate URLs while preserving readable formatting.
     */
    private function splitText(string $text): array
    {
        if (strlen($text) <= self::MAX_CHUNK_LENGTH) {
            return [$text];
        }

        $paragraphs = preg_split('/\n\s*\n/', $text) ?: [$text];
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }

            if (strlen($paragraph) <= self::MAX_CHUNK_LENGTH) {
                $candidate = $current === '' ? $paragraph : $current."\n\n".$paragraph;
                if (strlen($candidate) <= self::MAX_CHUNK_LENGTH) {
                    $current = $candidate;
                    continue;
                }

                if ($current !== '') {
                    $chunks[] = $current;
                }
                $current = $paragraph;
                continue;
            }

            if ($current !== '') {
                $chunks[] = $current;
                $current = '';
            }

            foreach ($this->splitOversizedParagraph($paragraph) as $part) {
                $chunks[] = $part;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks ?: [$text];
    }

    private function splitOversizedParagraph(string $text): array
    {
        $sentences = preg_split('/(?<=[.!?।])\s+/', $text) ?: [$text];
        $chunks = [];
        $current = '';

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if ($sentence === '') {
                continue;
            }

            if (strlen($sentence) > self::MAX_CHUNK_LENGTH) {
                if ($current !== '') {
                    $chunks[] = $current;
                    $current = '';
                }

                foreach (str_split($sentence, self::MAX_CHUNK_LENGTH) as $piece) {
                    $chunks[] = trim($piece);
                }
                continue;
            }

            $candidate = $current === '' ? $sentence : $current.' '.$sentence;
            if (strlen($candidate) <= self::MAX_CHUNK_LENGTH) {
                $current = $candidate;
            } else {
                $chunks[] = $current;
                $current = $sentence;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }
}
