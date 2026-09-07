<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('result_date');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->text('seo_keywords')->nullable()->after('seo_description');
            $table->string('canonical_url')->nullable()->after('seo_keywords');
        });
        Schema::create('job_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->string('action', 40)->default('updated');
            $table->json('snapshot');
            $table->string('changed_by')->nullable();
            $table->timestamps();
            $table->index(['job_id','created_at']);
        });
        Schema::create('job_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->string('title');
            $table->string('document_type', 40)->default('notification');
            $table->string('url');
            $table->timestamps();
            $table->index(['job_id','document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_documents');
        Schema::dropIfExists('job_versions');
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['seo_title','seo_description','seo_keywords','canonical_url']);
        });
    }
};