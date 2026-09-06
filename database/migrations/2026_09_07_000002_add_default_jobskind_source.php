<?php

use App\Models\JobSource;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        JobSource::firstOrCreate(
            ['fetch_url' => 'https://www.jobskind.com/'],
            [
                'name' => 'JobsKind',
                'base_url' => 'https://www.jobskind.com/',
                'source_type' => 'blogger',
                'frequency_minutes' => 30,
                'publish_mode' => 'approval',
                'default_category' => 'CG Vyapam',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        JobSource::where('fetch_url', 'https://www.jobskind.com/')->delete();
    }
};
