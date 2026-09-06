<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Str;

class JobPosterService
{
    public function render(Job $job): string
    {
        $template = $this->templateDataUri();
        $title = $this->escape(Str::limit($job->title_en ?: $job->title, 58, '…'));
        $releasedBy = $this->escape(Str::limit($job->source ?: $job->job_category, 52, '…'));
        $category = $this->escape($job->job_category ?: 'CGSSB');
        $department = $this->escape(Str::limit($job->department ?: 'Other Departments', 34, '…'));
        $posts = $this->escape($job->vacancies ?: '—');
        $start = $this->escape($job->application_start ?: 'जारी');
        $last = $this->escape($job->last_date ?: 'शीघ्र');

        if ($template) {
            $background = '<image href="'.$template.'" x="0" y="0" width="1214" height="1295" preserveAspectRatio="none"/>';
        } else {
            $background = '<rect width="1214" height="1295" fill="#f8fafc"/><rect x="0" y="0" width="1214" height="405" fill="#eaf4f7"/><text x="607" y="105" text-anchor="middle" font-family="Arial,sans-serif" font-size="64" font-weight="800" fill="#0f2747">CG<tspan fill="#16834a">Jobs</tspan></text><text x="607" y="155" text-anchor="middle" font-family="Arial,sans-serif" font-size="25" fill="#17324d">छत्तीसगढ़ की हर सरकारी नौकरी अब एक ही जगह</text>';
        }

        $svg = '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="1214" height="1295" viewBox="0 0 1214 1295">'.$background;
        $svg .= '<rect x="350" y="405" width="830" height="570" rx="35" fill="#fff" fill-opacity="0.97"/>';
        $svg .= '<rect x="1050" y="425" width="100" height="46" rx="18" fill="#ef2d2d"/><text x="1100" y="456" text-anchor="middle" font-family="Arial,sans-serif" font-size="25" font-weight="800" fill="#fff">NEW</text>';
        $svg .= '<text x="385" y="490" font-family="Arial,sans-serif" font-size="34" font-weight="800" fill="#0f2747">'.$title.'</text>';
        $svg .= '<text x="385" y="535" font-family="Arial,sans-serif" font-size="20" fill="#475569">Released by</text><text x="385" y="567" font-family="Arial,sans-serif" font-size="23" font-weight="700" fill="#111827">'.$releasedBy.'</text>';
        $svg .= '<rect x="382" y="600" width="766" height="105" rx="24" fill="#eef4f8"/><text x="510" y="640" font-family="Arial,sans-serif" font-size="18" fill="#475569">Total Post</text><text x="510" y="680" font-family="Arial,sans-serif" font-size="34" font-weight="800" fill="#111827">'.$posts.'</text><text x="770" y="640" font-family="Arial,sans-serif" font-size="18" fill="#475569">Department</text><text x="770" y="680" font-family="Arial,sans-serif" font-size="23" font-weight="800" fill="#111827">'.$department.'</text>';
        $svg .= '<rect x="382" y="720" width="766" height="108" rx="24" fill="#fff" stroke="#e5e7eb"/><text x="510" y="762" font-family="Arial,sans-serif" font-size="18" fill="#475569">Apply From</text><text x="510" y="802" font-family="Arial,sans-serif" font-size="25" font-weight="800" fill="#111827">'.$start.'</text><text x="840" y="762" font-family="Arial,sans-serif" font-size="18" fill="#ef2d2d">Last Date</text><text x="840" y="802" font-family="Arial,sans-serif" font-size="25" font-weight="800" fill="#ef2d2d">'.$last.'</text>';
        $svg .= '<rect x="382" y="850" width="515" height="75" rx="28" fill="#10965a"/><text x="640" y="898" text-anchor="middle" font-family="Arial,sans-serif" font-size="27" font-weight="800" fill="#fff">APPLY NOW →</text>';
        $svg .= '<rect x="925" y="850" width="223" height="75" rx="28" fill="#fff" stroke="#cbd5e1"/><text x="1036" y="898" text-anchor="middle" font-family="Arial,sans-serif" font-size="21" font-weight="700" fill="#111827">View Details →</text>';
        $svg .= '<text x="60" y="1135" font-family="Arial,sans-serif" font-size="24" font-weight="800" fill="#173d67">'.$category.'</text><text x="607" y="1185" text-anchor="middle" font-family="Arial,sans-serif" font-size="23" font-weight="700" fill="#173d67">सही जानकारी  |  सही अवसर  |  उज्ज्वल भविष्य</text>';
        $svg .= '</svg>';
        return $svg;
    }

    private function templateDataUri(): ?string
    {
        foreach (['job-poster-template.jpg','job-poster-template.jpeg','job-poster-template.png'] as $name) {
            $path = public_path('assets/'.$name);
            if (is_file($path)) {
                $mime = str_ends_with($name, '.png') ? 'image/png' : 'image/jpeg';
                return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
            }
        }
        return null;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
