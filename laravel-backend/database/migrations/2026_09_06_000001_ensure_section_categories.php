<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_sections') || !Schema::hasTable('categories')) {
            return;
        }

        $now = now();

        $sections = [
            'jobs' => [
                'name' => 'Jobs & Vacancies',
                'hindi_name' => 'सरकारी नौकरियां',
                'description' => 'छत्तीसगढ़ व केंद्र स्तर की सभी नई भर्तियां',
                'icon' => 'briefcase',
                'display_order' => 1,
            ],
            'news' => [
                'name' => 'Current Affairs & News',
                'hindi_name' => 'दैनिक समसामयिकी व समाचार',
                'description' => 'राज्य, राष्ट्रीय व अंतर्राष्ट्रीय करंट अफेयर्स',
                'icon' => 'newspaper',
                'display_order' => 2,
            ],
            'static_gk' => [
                'name' => 'Static GK & Study',
                'hindi_name' => 'सामान्य ज्ञान व अध्ययन',
                'description' => 'इतिहास, भूगोल, जनजाति व संस्कृति के तथ्य',
                'icon' => 'book',
                'display_order' => 3,
            ],
        ];

        foreach ($sections as $key => $data) {
            DB::table('app_sections')->updateOrInsert(
                ['section_key' => $key],
                array_merge($data, ['is_active' => true, 'updated_at' => $now, 'created_at' => $now])
            );
        }

        // Existing seeded categories are job categories. Assign them to Jobs
        // so they do not leak into Current Affairs or Static GK filters.
        DB::table('categories')
            ->whereNull('section_id')
            ->update(['section_id' => 'jobs', 'updated_at' => $now]);

        $categorySets = [
            'news' => [
                ['name' => 'Chhattisgarh', 'hindi_name' => 'छत्तीसगढ़', 'slug' => 'chhattisgarh-news', 'icon' => 'map', 'color' => '#1565C0', 'display_order' => 1],
                ['name' => 'National', 'hindi_name' => 'राष्ट्रीय', 'slug' => 'national-news', 'icon' => 'landmark', 'color' => '#2E7D32', 'display_order' => 2],
                ['name' => 'International', 'hindi_name' => 'अंतर्राष्ट्रीय', 'slug' => 'international-news', 'icon' => 'globe', 'color' => '#6A1B9A', 'display_order' => 3],
                ['name' => 'Economy', 'hindi_name' => 'अर्थव्यवस्था', 'slug' => 'economy-news', 'icon' => 'chart-line', 'color' => '#E65100', 'display_order' => 4],
                ['name' => 'Sports', 'hindi_name' => 'खेल', 'slug' => 'sports-news', 'icon' => 'futbol', 'color' => '#00838F', 'display_order' => 5],
                ['name' => 'Science & Technology', 'hindi_name' => 'विज्ञान एवं प्रौद्योगिकी', 'slug' => 'science-technology-news', 'icon' => 'flask', 'color' => '#C2185B', 'display_order' => 6],
                ['name' => 'Awards & Appointments', 'hindi_name' => 'पुरस्कार एवं नियुक्तियां', 'slug' => 'awards-appointments', 'icon' => 'award', 'color' => '#37474F', 'display_order' => 7],
            ],
            'static_gk' => [
                ['name' => 'History', 'hindi_name' => 'इतिहास', 'slug' => 'history-gk', 'icon' => 'landmark', 'color' => '#8D6E63', 'display_order' => 1],
                ['name' => 'Geography', 'hindi_name' => 'भूगोल', 'slug' => 'geography-gk', 'icon' => 'earth-asia', 'color' => '#2E7D32', 'display_order' => 2],
                ['name' => 'Indian Polity', 'hindi_name' => 'भारतीय राजव्यवस्था', 'slug' => 'polity-gk', 'icon' => 'scale-balanced', 'color' => '#1565C0', 'display_order' => 3],
                ['name' => 'Economy', 'hindi_name' => 'भारतीय अर्थव्यवस्था', 'slug' => 'economy-gk', 'icon' => 'chart-line', 'color' => '#E65100', 'display_order' => 4],
                ['name' => 'Science', 'hindi_name' => 'सामान्य विज्ञान', 'slug' => 'science-gk', 'icon' => 'flask', 'color' => '#6A1B9A', 'display_order' => 5],
                ['name' => 'Art & Culture', 'hindi_name' => 'कला एवं संस्कृति', 'slug' => 'art-culture-gk', 'icon' => 'palette', 'color' => '#C2185B', 'display_order' => 6],
                ['name' => 'Chhattisgarh GK', 'hindi_name' => 'छत्तीसगढ़ सामान्य ज्ञान', 'slug' => 'chhattisgarh-gk', 'icon' => 'map', 'color' => '#00838F', 'display_order' => 7],
            ],
        ];

        foreach ($categorySets as $sectionKey => $categories) {
            foreach ($categories as $category) {
                DB::table('categories')->updateOrInsert(
                    ['slug' => $category['slug']],
                    array_merge($category, [
                        'section_id' => $sectionKey,
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ])
                );
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        DB::table('categories')
            ->whereIn('slug', [
                'chhattisgarh-news', 'national-news', 'international-news',
                'economy-news', 'sports-news', 'science-technology-news',
                'awards-appointments', 'history-gk', 'geography-gk',
                'polity-gk', 'economy-gk', 'science-gk', 'art-culture-gk',
                'chhattisgarh-gk',
            ])
            ->delete();
    }
};
