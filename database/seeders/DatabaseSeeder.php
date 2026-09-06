<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Categories
        $categories = [
            ['name' => 'All', 'hindi_name' => 'सभी', 'slug' => 'all', 'color' => '#1565C0', 'display_order' => 1],
            ['name' => 'CGPSC', 'hindi_name' => 'छत्तीसगढ़ लोक सेवा', 'slug' => 'cgpsc', 'color' => '#2E7D32', 'display_order' => 2],
            ['name' => 'CG Vyapam', 'hindi_name' => 'व्यापम', 'slug' => 'vyapam', 'color' => '#E65100', 'display_order' => 3],
            ['name' => 'CG Police', 'hindi_name' => 'पुलिस भर्ती', 'slug' => 'police', 'color' => '#C2185B', 'display_order' => 4],
            ['name' => 'CG Education', 'hindi_name' => 'शिक्षक भर्ती', 'slug' => 'education', 'color' => '#00838F', 'display_order' => 5],
            ['name' => 'CG Health', 'hindi_name' => 'स्वास्थ्य विभाग', 'slug' => 'health', 'color' => '#6A1B9A', 'display_order' => 6],
            ['name' => 'Central Jobs', 'hindi_name' => 'केंद्रीय भर्ती', 'slug' => 'central', 'color' => '#37474F', 'display_order' => 7],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 2. Seed Jobs
        $jobs = [
            [
                'custom_id' => 'cgpsc-sse-2026',
                'title' => 'CGPSC राज्य सेवा परीक्षा 2026 - 242 पदों पर भर्ती',
                'summary' => 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC) द्वारा राज्य प्रशासनिक सेवा, उप पुलिस अधीक्षक एवं अन्य 242 पदों हेतु ऑनलाइन आवेदन आमंत्रित किए गए हैं।',
                'detailed_content' => "छत्तीसगढ़ लोक सेवा आयोग (CGPSC) ने राज्य सेवा परीक्षा 2026 की आधिकारिक अधिसूचना जारी कर दी है।\n\nपदों का विवरण:\n• डिप्टी कलेक्टर: 15 पद\n• डीएसपी (DSP): 22 पद\n• नायब तहसीलदार: 40 पद\n• वाणिज्यिक कर निरीक्षक: 35 पद\n• अन्य पद: 130 पद\n\nचयन प्रक्रिया:\n1. प्रारंभिक परीक्षा (वस्तुनिष्ठ प्रकार)\n2. मुख्य लिखित परीक्षा\n3. साक्षात्कार एवं व्यक्तित्व परीक्षण\n\nअभ्यर्थी आधिकारिक वेबसाइट psc.cg.gov.in पर जाकर ऑनलाइन आवेदन कर सकते हैं।",
                'category' => 'CGPSC',
                'source' => 'psc.cg.gov.in',
                'source_url' => 'https://psc.cg.gov.in',
                'image_url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800',
                'published_at' => '2026-09-01',
                'relative_time' => '1 दिन पहले',
                'is_breaking' => true,
                'is_new' => true,
                'vacancies' => '242 पद',
                'eligibility' => 'मान्यता प्राप्त विश्वविद्यालय से स्नातक (Graduation) की उपाधि',
                'age_limit' => '21 से 35 वर्ष (छ.ग. के मूल निवासियों को नियमानुसार 5 वर्ष की छूट)',
                'selection_process' => 'प्रारंभिक परीक्षा (Prelims), मुख्य परीक्षा (Mains) एवं साक्षात्कार (Interview)',
                'official_notification_url' => 'https://psc.cg.gov.in/pdf/advt2026.pdf',
                'apply_url' => 'https://psc.cg.gov.in/online_app',
                'application_start' => '10 Sep 2026',
                'last_date' => '10 Oct 2026',
                'exam_date' => '14 Dec 2026',
                'admit_card_date' => '05 Dec 2026',
                'result_date' => 'Jan 2027',
            ],
            [
                'custom_id' => 'cg-police-constable-2026',
                'title' => 'CG Police Constable भर्ती 2026 - 5,967 आरक्षक पदों पर आवेदन',
                'summary' => 'छत्तीसगढ़ पुलिस मुख्यालय रायपुर द्वारा विभिन्न जिलों में 5967 आरक्षक (जीडी/ट्रेड/चालक) पदों पर भर्ती की प्रक्रिया शुरू।',
                'detailed_content' => "छत्तीसगढ़ पुलिस विभाग में युवाओं के लिए बंपर भर्ती!\n\nशारीरिक माप परीक्षण (PST):\n• पुरुष (सामान्य/ओबीसी): ऊंचाई 168 सेमी, सीना 81-86 सेमी\n• महिला: ऊंचाई 158 सेमी\n• आरक्षित वर्ग को नियमानुसार विशेष छूट प्रदान की जाएगी।\n\nदौड़ परीक्षा (Physical Efficiency Test):\n1500 मीटर दौड़, लंबी कूद, ऊंची कूद एवं गोला फेंक।",
                'category' => 'CG Police',
                'source' => 'cgpolice.gov.in',
                'source_url' => 'https://cgpolice.gov.in',
                'image_url' => 'https://images.unsplash.com/photo-1541872703-74c5e44368f9?w=800',
                'published_at' => '2026-09-02',
                'relative_time' => '2 दिन पहले',
                'is_breaking' => true,
                'is_new' => true,
                'vacancies' => '5,967 पद',
                'eligibility' => '10वीं / 12वीं उत्तीर्ण (नक्सल प्रभावित क्षेत्रों के लिए 8वीं पास)',
                'age_limit' => '18 से 28 वर्ष (छ.ग. युवाओं हेतु 5 वर्ष छूट)',
                'selection_process' => 'दस्तावेज सत्यापन, शारीरिक माप परीक्षण (PST), शारीरिक दक्षता (PET) एवं लिखित परीक्षा',
                'official_notification_url' => 'https://cgpolice.gov.in/recruitment',
                'apply_url' => 'https://cgpolice.gov.in/apply',
                'application_start' => 'जारी है',
                'last_date' => '25 Oct 2026',
                'exam_date' => 'Nov 2026',
                'admit_card_date' => 'Oct 2026',
            ],
            [
                'custom_id' => 'cg-vyapam-tet-2026',
                'title' => 'CG Vyapam शिक्षक पात्रता परीक्षा (TET) 2026 अधिसूचना जारी',
                'summary' => 'छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (CG Vyapam) ने शिक्षक पात्रता परीक्षा 2026 के लिए परीक्षा तिथि एवं दिशानिर्देश जारी किए।',
                'detailed_content' => "छत्तीसगढ़ व्यापम द्वारा प्राथमिक (कक्षा 1 से 5) एवं उच्च प्राथमिक (कक्षा 6 से 8) शालाओं में शिक्षकों की पात्रता हेतु TET 2026 आयोजित की जा रही है।\n\nपरीक्षा दो पालियों में आयोजित होगी:\n• प्रथम पाली: प्राथमिक स्तर\n• द्वितीय पाली: माध्यमिक स्तर",
                'category' => 'CG Vyapam',
                'source' => 'vyapam.cgstate.gov.in',
                'source_url' => 'https://vyapam.cgstate.gov.in',
                'image_url' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=800',
                'published_at' => '2026-09-03',
                'relative_time' => '3 दिन पहले',
                'is_breaking' => false,
                'is_new' => true,
                'vacancies' => 'पात्रता परीक्षा (TET)',
                'eligibility' => 'D.El.Ed. / B.Ed. अथवा समकक्ष शैक्षणिक योग्यता',
                'age_limit' => 'न्यूनतम 18 वर्ष',
                'selection_process' => 'वस्तुनिष्ठ बहुविकल्पीय लिखित परीक्षा',
                'official_notification_url' => 'https://vyapam.cgstate.gov.in/tet2026',
                'apply_url' => 'https://vyapam.cgstate.gov.in/apply-tet',
                'application_start' => '01 Sep 2026',
                'last_date' => '30 Sep 2026',
                'exam_date' => '25 Oct 2026',
                'admit_card_date' => '15 Oct 2026',
            ]
        ];

        foreach ($jobs as $jobData) {
            Job::updateOrCreate(['custom_id' => $jobData['custom_id']], $jobData);
        }

        // 3. Seed Alerts
        $alerts = [
            [
                'custom_id' => 'alert-1',
                'category' => 'CG Vyapam',
                'title' => 'व्यापम शिक्षक पात्रता परीक्षा TET 2026 एडमिट कार्ड जारी!',
                'short_description' => 'परीक्षार्थी अपने रोल नंबर अथवा पंजीकृत मोबाइल नंबर से व्यापम पोर्टल पर प्रवेश पत्र डाउनलोड कर सकते हैं।',
                'time' => '15 मिनट पहले',
                'type' => 'ADMIT_CARD',
                'action_url' => 'https://vyapam.cgstate.gov.in',
                'is_broadcasted' => true,
            ],
            [
                'custom_id' => 'alert-2',
                'category' => 'CG Police',
                'title' => 'पुलिस आरक्षक भर्ती शारीरिक नाप-जोख की नई तिथि घोषित',
                'short_description' => 'रायपुर, दुर्ग व बिलासपुर रेंज के अभ्यर्थियों के लिए एडमिट कार्ड जारी कर दिए गए हैं।',
                'time' => '1 घंटा पहले',
                'type' => 'BREAKING',
                'action_url' => 'https://cgpolice.gov.in',
                'is_broadcasted' => true,
            ],
            [
                'custom_id' => 'alert-3',
                'category' => 'CGPSC',
                'title' => 'CGPSC राज्य सेवा परीक्षा 2025 अंतिम परिणाम व मेरिट सूची घोषित',
                'short_description' => 'आधिकारिक वेबसाइट पर चयनित अभ्यर्थियों के रोल नंबर तथा कट-ऑफ मार्क्स जारी कर दिए गए हैं।',
                'time' => '3 घंटे पहले',
                'type' => 'RESULT',
                'action_url' => 'https://psc.cg.gov.in',
                'is_broadcasted' => false,
            ]
        ];

        foreach ($alerts as $a) {
            Alert::updateOrCreate(['custom_id' => $a['custom_id']], $a);
        }
    }
}
