<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('ai_provider_settings')
            ->where('provider', 'gemini')
            ->where(function ($q) {
                $q->whereNull('model')->orWhere('model', '')->orWhere('model', 'gemini-2.5-flash');
            })
            ->update(['model' => 'gemini-3.8-flash']);
    }

    public function down(): void
    {
        DB::table('ai_provider_settings')
            ->where('provider', 'gemini')
            ->where('model', 'gemini-3.8-flash')
            ->update(['model' => 'gemini-2.5-flash']);
    }
};
