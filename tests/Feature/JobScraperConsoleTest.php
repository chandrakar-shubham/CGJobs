<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobImport;
use App\Models\JobSource;
use App\Models\User;
use App\Services\JobBatchAiService;
use App\Services\JobScrapers\CgpscScraper;
use App\Services\JobScrapers\CentralGovtScraper;
use App\Services\JobScrapers\ErojgarScraper;
use App\Services\JobScrapers\JobskindScraper;
use App\Services\JobScrapers\VyapamScraper;
use App\Services\UnifiedJobScraperPipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobScraperConsoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@cgjobs.com',
            'role' => 'admin',
        ]);
    }

    public function test_scraper_console_screen_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.jobs.scraper-console'));

        $response->assertStatus(200);
        $response->assertSee('भर्ती स्क्रैपर व एआई कंट्रोल कंसोल');
        $response->assertSee('स्टेजिंग ग्रिड खाली है');
    }

    public function test_cgpsc_scraper_parses_html_table_into_normalized_records(): void
    {
        $mockHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
    <table class="table">
        <tr><th>SN</th><th>Advertisement Title</th><th>Date</th><th>Download</th></tr>
        <tr>
            <td>1</td>
            <td>State Service Examination 2026 Notification for Deputy Collector, DSP and Other Posts</td>
            <td>10/01/2026</td>
            <td><a href="Advt_SSE_2026.pdf">Download</a></td>
        </tr>
    </table>
</body>
</html>
HTML;

        Http::fake([
            'https://psc.cg.gov.in/*' => Http::response($mockHtml, 200),
        ]);

        $scraper = app(CgpscScraper::class);
        $records = $scraper->scrape();

        $this->assertNotEmpty($records);
        $first = $records[0];
        $this->assertEquals('CGPSC', $first['category']);
        $this->assertEquals('job', $first['post_type']);
        $this->assertEquals('छत्तीसगढ़ लोक सेवा आयोग (CGPSC)', $first['source_name']);
        $this->assertStringContainsString('State Service Examination 2026', $first['title']);
    }

    public function test_vyapam_scraper_parses_admit_card_and_job_endpoints(): void
    {
        $mockHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
    <table>
        <tr>
            <td>Teacher Eligibility Test (CG TET 2026) Online Admit Card Release Notice</td>
            <td>05/02/2026</td>
            <td><a href="TET_AdmitCard_Press.pdf">Press Release</a></td>
        </tr>
    </table>
</body>
</html>
HTML;

        Http::fake([
            'https://vyapamcg.cgstate.gov.in/*' => Http::response($mockHtml, 200),
        ]);

        $scraper = app(VyapamScraper::class);
        $records = $scraper->scrape(['post_types' => ['admit_card']]);

        $this->assertNotEmpty($records);
        $first = $records[0];
        $this->assertEquals('CGSSB', $first['category']);
        $this->assertEquals('admit_card', $first['post_type']);
        $this->assertEquals('छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (CG Vyapam)', $first['source_name']);
        $this->assertStringContainsString('TET', $first['title']);
    }

    public function test_jobskind_scraper_masks_source_and_classifies_correctly(): void
    {
        $mockRss = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
    <title>Jobskind Feed</title>
    <item>
        <title>CG Health Department Medical Officer Recruitment 2026 for 400 Vacancies</title>
        <link>https://jobskind.com/health-dept-mo-recruitment-2026/</link>
        <description>Chhattisgarh Health and Family Welfare Department recruitment for medical officers with MBBS qualification.</description>
        <pubDate>Mon, 01 Feb 2026 10:00:00 GMT</pubDate>
    </item>
</channel>
</rss>
XML;

        Http::fake([
            'https://www.jobskind.com/*' => Http::response($mockRss, 200),
        ]);

        $scraper = app(JobskindScraper::class);
        $records = $scraper->scrape();

        $this->assertNotEmpty($records);
        $first = $records[0];
        // Strict Masking test: Never show Jobskind as public source
        $this->assertNotEquals('jobskind.com', $first['source_name']);
        $this->assertStringContainsString('शासकीय भर्ती', $first['source_name']);
        $this->assertEquals('Health', $first['department']);
    }

    public function test_pipeline_stages_scraped_items_into_job_imports(): void
    {
        $mockHtml = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
    <table>
        <tr>
            <td>Veterinary Assistant Surgeon Recruitment 2026 Examination Details</td>
            <td>01/01/2026</td>
            <td><a href="Advt_VAS_2026.pdf">PDF</a></td>
        </tr>
    </table>
</body>
</html>
HTML;

        Http::fake([
            'https://psc.cg.gov.in/*' => Http::response($mockHtml, 200),
        ]);

        $pipeline = app(UnifiedJobScraperPipeline::class);
        $result = $pipeline->scrapeAndStage([
            'sources' => ['cgpsc'],
            'post_types' => ['job'],
        ]);

        $this->assertGreaterThanOrEqual(1, $result['fetched']);
        $this->assertGreaterThanOrEqual(1, $result['staged']);

        $this->assertDatabaseHas('job_imports', [
            'status' => 'staged',
            'job_category' => 'CGPSC',
        ]);
    }

    public function test_batch_gemini_processes_staged_items_into_structured_drafts(): void
    {
        // 1. Create a staged job import
        $source = JobSource::create([
            'name' => 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC)',
            'base_url' => 'https://psc.cg.gov.in',
            'fetch_url' => 'https://psc.cg.gov.in/Advertisement.htm',
            'source_type' => 'html',
            'default_category' => 'CGPSC',
            'publish_mode' => 'approval',
            'is_active' => true,
        ]);

        $import = JobImport::create([
            'job_source_id' => $source->id,
            'external_url' => 'https://psc.cg.gov.in/test-advt-101.pdf',
            'external_key' => 'test-key-101',
            'title' => 'CGPSC Veterinary Assistant Surgeon 2026 Online Application Form',
            'summary' => 'Applications invited for Veterinary Assistant Surgeon in Animal Husbandry Dept.',
            'content' => 'Complete notice text with 80 posts and salary scale 56100.',
            'category' => 'Agriculture',
            'job_category' => 'CGPSC',
            'department' => 'Agriculture',
            'published_by' => 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC)',
            'status' => 'staged',
        ]);

        // Mock Gemini Batch API response
        $geminiResponse = [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'batch_results' => [
                                        [
                                            'id' => $import->id,
                                            'designation' => 'Veterinary Assistant Surgeon',
                                            'conducting_agency' => 'CGPSC',
                                            'department' => 'Agriculture',
                                            'vacancies' => '80',
                                            'salary' => '₹56,100 - ₹1,77,500 (Level 12)',
                                            'qualification' => 'B.V.Sc. & A.H. Degree and Registration in CG Veterinary Council',
                                            'age_limit' => '21 to 30 years (40 years for CG residents)',
                                            'selection_process' => 'Online Written Examination and Interview',
                                            'application_start' => '15/01/2026',
                                            'last_date' => '15/02/2026',
                                            'exam_date' => '25/03/2026',
                                            'summary_60_words' => 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC) द्वारा पशुधन विकास विभाग में पशु चिकित्सा सहायक शल्यज्ञ (VAS) के कुल 80 पदों पर भर्ती निकाली गई है। न्यूनतम योग्यता B.V.Sc डिग्री तथा आयु सीमा 21 से 40 वर्ष निर्धारित है। इच्छुक अभ्यर्थी 15 फरवरी 2026 तक ऑनलाइन आवेदन कर सकते हैं।',
                                            'detailed_web_article_html' => '<h2>CGPSC पशु चिकित्सा सहायक शल्यज्ञ भर्ती 2026</h2><p>छत्तीसगढ़ शासन ने पशुपालन विभाग में 80 पदों हेतु विस्तृत विज्ञापन जारी किया है।</p>',
                                            'seo_keywords' => ['cgpsc', 'veterinary assistant surgeon', 'cg jobs 2026'],
                                        ]
                                    ]
                                ])
                            ]
                        ]
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response($geminiResponse, 200),
        ]);

        $aiService = app(JobBatchAiService::class);
        $result = $aiService->processBatch([
            [
                'id' => $import->id,
                'category' => $import->job_category,
                'title' => $import->title,
                'raw_content' => $import->content,
                'source_name' => $import->published_by,
            ]
        ]);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['processed']);

        // Create job record
        $job = $aiService->createOrUpdateJobRecord($import, $result['processed'][0]);
        $import->update([
            'status' => 'processed',
            'job_id' => $job->id,
        ]);

        // Assert job created with workflow_status = draft
        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'workflow_status' => 'draft',
            'vacancies' => '80',
            'salary' => '₹56,100 - ₹1,77,500 (Level 12)',
            'job_category' => 'CGPSC',
            'department' => 'Agriculture',
        ]);

        $this->assertStringContainsString('60 पदों पर भर्ती निकाली गई है', $job->summary);
        $this->assertStringContainsString('<h2>CGPSC', $job->detailed_content);
    }

    public function test_publish_draft_job_transitions_to_live(): void
    {
        $job = Job::create([
            'title' => 'Sample Draft Job for Publish Test',
            'slug' => 'sample-draft-job-123',
            'summary' => 'Short summary for test',
            'detailed_content' => '<p>Detailed article</p>',
            'workflow_status' => 'draft',
            'job_category' => 'CGSSB',
            'department' => 'Revenue',
            'source' => 'छत्तीसगढ़ व्यावसायिक परीक्षा मंडल (CG Vyapam)',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.jobs.scraper-console.publish', $job));

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'workflow_status' => 'published',
            'published_at' => now()->format('Y-m-d'),
        ]);
    }

    public function test_schedule_draft_job_sets_scheduled_time(): void
    {
        $job = Job::create([
            'title' => 'Sample Draft Job for Schedule Test',
            'slug' => 'sample-draft-job-456',
            'summary' => 'Short summary for test',
            'detailed_content' => '<p>Detailed article</p>',
            'workflow_status' => 'draft',
            'job_category' => 'CGPSC',
            'department' => 'Police',
            'source' => 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC)',
        ]);

        $scheduleTime = now()->addDays(2)->format('Y-m-d H:i:s');

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.jobs.scraper-console.schedule', $job), [
                'scheduled_at' => $scheduleTime,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'workflow_status' => 'scheduled',
            'scheduled_at' => $scheduleTime,
        ]);
    }
}
