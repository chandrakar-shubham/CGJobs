<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSection;
use Illuminate\Http\JsonResponse;

class SectionApiController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = AppSection::with(['categories' => function ($q) {
            $q->where('is_active', true)->orderBy('display_order', 'asc');
        }])->where('is_active', true)->orderBy('display_order', 'asc')->get();

        if ($sections->isEmpty()) {
            return response()->json([
                'success' => true,
                'sections' => [
                    [
                        'id' => 'jobs',
                        'name' => 'Jobs & Vacancies',
                        'hindiName' => 'छत्तीसगढ़ सरकारी नौकरियां',
                        'description' => 'छत्तीसगढ़ व केंद्र स्तर की सभी नई भर्तियां',
                        'icon' => 'briefcase',
                        'isActive' => true,
                        'categories' => [
                            ['id' => 'all', 'name' => 'All', 'hindiName' => 'सभी', 'section' => 'jobs'],
                            ['id' => 'cgpsc', 'name' => 'CGPSC', 'hindiName' => 'लोक सेवा आयोग', 'section' => 'jobs'],
                            ['id' => 'vyapam', 'name' => 'CG Vyapam', 'hindiName' => 'व्यापम', 'section' => 'jobs'],
                            ['id' => 'police', 'name' => 'CG Police', 'hindiName' => 'पुलिस भर्ती', 'section' => 'jobs'],
                            ['id' => 'education', 'name' => 'CG Education', 'hindiName' => 'शिक्षक भर्ती', 'section' => 'jobs'],
                            ['id' => 'health', 'name' => 'CG Health', 'hindiName' => 'स्वास्थ्य विभाग', 'section' => 'jobs'],
                        ]
                    ],
                    [
                        'id' => 'news',
                        'name' => 'Current Affairs & News',
                        'hindiName' => 'दैनिक समसामयिकी व समाचार',
                        'description' => 'राज्य, राष्ट्रीय व अंतर्राष्ट्रीय करंट अफेयर्स',
                        'icon' => 'newspaper',
                        'isActive' => true,
                        'categories' => [
                            ['id' => 'all', 'name' => 'All', 'hindiName' => 'सभी', 'section' => 'news'],
                            ['id' => 'cg_state', 'name' => 'Chhattisgarh', 'hindiName' => 'छत्तीसगढ़ समसामयिकी', 'section' => 'news'],
                            ['id' => 'national', 'name' => 'National', 'hindiName' => 'राष्ट्रीय', 'section' => 'news'],
                            ['id' => 'schemes', 'name' => 'Govt Schemes', 'hindiName' => 'सरकारी योजनाएं', 'section' => 'news'],
                        ]
                    ],
                    [
                        'id' => 'static_gk',
                        'name' => 'Static GK & Study',
                        'hindiName' => 'सामान्य ज्ञान व अध्ययन',
                        'description' => 'इतिहास, भूगोल, जनजाति व संस्कृति के तथ्य',
                        'icon' => 'book',
                        'isActive' => true,
                        'categories' => [
                            ['id' => 'all', 'name' => 'All Topics', 'hindiName' => 'सभी विषय', 'section' => 'static_gk'],
                            ['id' => 'history', 'name' => 'History', 'hindiName' => 'इतिहास', 'section' => 'static_gk'],
                            ['id' => 'geography', 'name' => 'Geography', 'hindiName' => 'भूगोल', 'section' => 'static_gk'],
                            ['id' => 'tribes', 'name' => 'Tribes & Culture', 'hindiName' => 'जनजाति व संस्कृति', 'section' => 'static_gk'],
                            ['id' => 'economy', 'name' => 'Economy', 'hindiName' => 'अर्थव्यवस्था', 'section' => 'static_gk'],
                        ]
                    ],
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'sections' => $sections->map(fn($s) => $s->toApiArray()),
        ]);
    }
}
