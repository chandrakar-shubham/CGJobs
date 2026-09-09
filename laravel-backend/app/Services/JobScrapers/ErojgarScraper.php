<?php

namespace App\Services\JobScrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErojgarScraper
{
    protected string $baseUrl = 'https://erojgar.cg.gov.in';
    protected string $endpoint = 'https://erojgar.cg.gov.in/LandingSite/en/Dist_Recruitments.aspx';

    public static array $chhattisgarhDistricts = [
        'All' => 'All Chhattisgarh',
        'Raipur' => 'रायपुर (Raipur)',
        'Bilaspur' => 'बिलासपुर (Bilaspur)',
        'Durg' => 'दुर्ग (Durg)',
        'Rajnandgaon' => 'राजनंदगांव (Rajnandgaon)',
        'Bastar' => 'बस्तर (Bastar / Jagdalpur)',
        'Surguja' => 'सरगुजा (Surguja / Ambikapur)',
        'Korba' => 'कोरबा (Korba)',
        'Raigarh' => 'रायगढ़ (Raigarh)',
        'Janjgir-Champa' => 'जांजगीर-चांपा (Janjgir-Champa)',
        'Mahasamund' => 'महासमुंद (Mahasamund)',
        'Dhamtari' => 'धमतरी (Dhamtari)',
        'Kanker' => 'कांकेर (Kanker)',
        'Kabirdham' => 'कबीरधाम (Kawardha)',
        'Balod' => 'बालोद (Balod)',
        'Bemetara' => 'बेमेतरा (Bemetara)',
        'Baloda Bazar' => 'बलौदाबाजार (Baloda Bazar)',
        'Gariaband' => 'गरियाबंद (Gariaband)',
        'Mungeli' => 'मुंगेली (Mungeli)',
        'Kondagaon' => 'कोंडागांव (Kondagaon)',
        'Narayanpur' => 'नारायणपुर (Narayanpur)',
        'Bijapur' => 'बीजापुर (Bijapur)',
        'Dantewada' => 'दंतेवाड़ा (Dantewada)',
        'Sukma' => 'सुकमा (Sukma)',
        'Korea' => 'कोरिया (Korea)',
        'Surajpur' => 'सूरजपुर (Surajpur)',
        'Balrampur' => 'बलरामपुर (Balrampur)',
        'Jashpur' => 'जशपुर (Jashpur)',
        'Gaurela-Pendra-Marwahi' => 'गौरेला-पेंड्रा-मरवाही',
        'Khairagarh-Chhuikhadan-Gandai' => 'खैरागढ़-छुईखदान-गंडई',
        'Mohla-Manpur-Ambagarh Chowki' => 'मोहला-मानपुर-अं. चौकी',
        'Sarangarh-Bilaigarh' => 'सारंगढ़-बिलाईगढ़',
        'Sakti' => 'सक्ती (Sakti)',
        'Manendragarh-Chirmiri-Bharatpur' => 'मनेंद्रगढ़-चिरमिरी-भरतपुर',
    ];

    /**
     * Scrape E-Rojgar District Recruitments
     *
     * @param array $options ['district' => '...', 'from' => '...', 'to' => '...']
     * @return array Standardized raw scraped records
     */
    public function scrape(array $options = []): array
    {
        $records = [];

        try {
            // 1. Initial GET request to obtain ASP.NET state tokens & default table
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($this->endpoint);

            if (!$response->successful()) {
                Log::warning("E-Rojgar initial GET failed: Status " . $response->status());
                return [];
            }

            $html = $response->body();
            $cookies = $response->cookies();

            // Extract default active recruitments on the page
            $items = $this->parseRows($html, 'All Chhattisgarh');
            $records = array_merge($records, $items);

            // If a specific district was requested and it is not 'All'
            $selectedDistrict = $options['district'] ?? 'All';
            if ($selectedDistrict !== 'All' && !empty($selectedDistrict)) {
                $districtItems = $this->queryDistrict($html, $cookies, $selectedDistrict);
                $records = array_merge($records, $districtItems);
            }

        } catch (\Throwable $e) {
            Log::error("E-Rojgar Scraper error: " . $e->getMessage());
        }

        // Apply date filtering
        $filtered = [];
        foreach ($records as $item) {
            if (!empty($options['from']) && !empty($item['published_at'])) {
                if ($item['published_at'] < $options['from']) continue;
            }
            if (!empty($options['to']) && !empty($item['published_at'])) {
                if ($item['published_at'] > $options['to']) continue;
            }
            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * Postback for a specific district dropdown value in ASP.NET
     */
    protected function queryDistrict(string $initialHtml, $cookies, string $district): array
    {
        $viewState = $this->extractInput($initialHtml, '__VIEWSTATE');
        $eventValidation = $this->extractInput($initialHtml, '__EVENTVALIDATION');
        $viewStateGenerator = $this->extractInput($initialHtml, '__VIEWSTATEGENERATOR');

        if (!$viewState) return [];

        try {
            $postData = [
                '__EVENTTARGET' => 'ddlDistrict',
                '__EVENTARGUMENT' => '',
                '__VIEWSTATE' => $viewState,
                '__VIEWSTATEGENERATOR' => $viewStateGenerator,
                '__EVENTVALIDATION' => $eventValidation,
                'ddlDistrict' => $district,
            ];

            $resp = Http::timeout(15)
                ->withCookies($cookies->toArray(), 'erojgar.cg.gov.in')
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Referer' => $this->endpoint,
                ])
                ->asForm()
                ->post($this->endpoint, $postData);

            if ($resp->successful()) {
                return $this->parseRows($resp->body(), $district);
            }
        } catch (\Throwable $e) {
            Log::warning("E-Rojgar district query failed for {$district}: " . $e->getMessage());
        }

        return [];
    }

    protected function parseRows(string $html, string $district): array
    {
        $items = [];
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $rows = $xpath->query('//table//tr');

        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            if ($cells->length < 3) continue;

            $districtName = $district;
            $office = '';
            $title = '';
            $dates = '';
            $pdfUrl = null;

            // Gather text from cells
            $texts = [];
            foreach ($cells as $c) {
                $texts[] = trim(preg_replace('/\s+/', ' ', $c->textContent));
            }

            // Identify title & dates
            $combined = implode(' | ', $texts);
            if (strlen($combined) < 15) continue;

            // Look for link in row
            $links = $xpath->query('.//a[@href]', $row);
            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                if (!empty($href)) {
                    $pdfUrl = $this->normalizeUrl($href);
                    break;
                }
            }

            $rawTitle = $texts[1] ?? $texts[0];
            if (empty($rawTitle) || strlen($rawTitle) < 8) {
                $rawTitle = $combined;
            }

            $publishedAt = $this->extractDate($combined);

            $items[] = [
                'source_key' => 'erojgar',
                'source_name' => 'शासकीय संविदा भर्ती (छत्तीसगढ़ शासन / ई-रोजगार)',
                'source_internal' => 'erojgar.cg.gov.in',
                'category' => 'CONTRACTUAL',
                'post_type' => 'job',
                'title' => $rawTitle,
                'district' => $districtName,
                'raw_content' => "जिला: {$districtName} | " . $combined,
                'published_at' => $publishedAt ?: date('Y-m-d'),
                'official_notification_url' => $pdfUrl,
                'apply_url' => $this->endpoint,
                'official_website' => $this->baseUrl,
                'source_url' => $pdfUrl ?: $this->endpoint,
                'department' => 'Other Departments',
            ];
        }

        return $items;
    }

    protected function extractInput(string $html, string $name): ?string
    {
        if (preg_match('/id="' . preg_quote($name, '/') . '"\s+value="([^"]*)"/i', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/name="' . preg_quote($name, '/') . '"\s+value="([^"]*)"/i', $html, $m)) {
            return $m[1];
        }
        return null;
    }

    protected function extractDate(string $text): ?string
    {
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        return null;
    }

    protected function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
