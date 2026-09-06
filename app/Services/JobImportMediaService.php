<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class JobImportMediaService
{
    public function extract(string $html, string $pageUrl): array
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $main = null;
        foreach ([
            '//meta[@property="og:image"]/@content',
            '//meta[@name="twitter:image"]/@content',
            '//meta[@property="twitter:image"]/@content',
            '//*[@itemprop="image"]/@content',
            '//*[@itemprop="image"]/@src',
        ] as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length) {
                $candidate = trim((string) $nodes->item(0)->nodeValue);
                if ($candidate) { $main = $this->absolute($candidate, $pageUrl); if ($main) break; }
            }
        }

        $images = [];
        $body = null;
        foreach ([
            '//article',
            '//*[contains(concat(" ", normalize-space(@class), " "), " post-body ")]',
            '//*[contains(concat(" ", normalize-space(@class), " "), " entry-content ")]',
            '//*[contains(concat(" ", normalize-space(@class), " "), " post-content ")]',
            '//main',
        ] as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length && mb_strlen(trim($nodes->item(0)->textContent)) > 250) {
                $body = $nodes->item(0);
                break;
            }
        }

        if ($body) {
            foreach ($xpath->query('.//img', $body) as $img) {
                $src = trim((string) (
                    $img->getAttribute('src')
                    ?: $img->getAttribute('data-src')
                    ?: $img->getAttribute('data-lazy-src')
                    ?: $img->getAttribute('data-original')
                ));
                if (!$src && $img->hasAttribute('srcset')) {
                    $src = trim(explode(',', $img->getAttribute('srcset'))[0] ?? '');
                    $src = trim(preg_replace('/\s+\S+$/', '', $src));
                }
                $url = $this->absolute($src, $pageUrl);
                if ($url && !$this->looksLikeUiImage($url, $img->getAttribute('alt').' '.$img->getAttribute('class'))) {
                    $images[] = $url;
                }
            }
        }

        $images = array_values(array_unique($images));
        if ($main && !in_array($main, $images, true)) array_unshift($images, $main);

        return [
            'image_url' => $main ?: ($images[0] ?? null),
            'image_urls' => array_slice($images, 0, 30),
        ];
    }

    private function absolute(string $value, string $base): ?string
    {
        $value = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($value === '' || str_starts_with($value, 'data:')) return null;
        if (str_starts_with($value, '//')) return 'https:'.$value;
        if (filter_var($value, FILTER_VALIDATE_URL)) return $value;

        $parts = parse_url($base);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) return null;
        $origin = $parts['scheme'].'://'.$parts['host'].(!empty($parts['port']) ? ':'.$parts['port'] : '');
        if (str_starts_with($value, '/')) return $origin.$value;

        $path = $parts['path'] ?? '/';
        $dir = rtrim(str_replace('\\', '/', dirname($path)), '/');
        return $origin.($dir ? $dir.'/' : '/').ltrim($value, '/');
    }

    private function looksLikeUiImage(string $url, string $hint): bool
    {
        $value = strtolower($url.' '.$hint);
        return (bool) preg_match('/(logo|icon|favicon|avatar|emoji|sprite|tracking|pixel|blank|spinner|social|share|whatsapp|facebook|twitter|telegram)/i', $value);
    }
}
