<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hongvan_product_translations', function (Blueprint $table): void {
            $table->text('search_text')->nullable()->comment('Unicode-normalized accent-folded text maintained by the application for safe multilingual FULLTEXT matching');
            $table->fullText('search_text', 'hv_p41_product_search_ft');
        });
        Schema::table('hongvan_crop_solution_translations', function (Blueprint $table): void {
            $table->text('search_text')->nullable()->comment('Unicode-normalized accent-folded text maintained by the application for safe multilingual FULLTEXT matching');
            $table->fullText('search_text', 'hv_p41_crop_solution_search_ft');
        });
        Schema::table('hongvan_service_translations', function (Blueprint $table): void {
            $table->text('search_text')->nullable()->comment('Unicode-normalized accent-folded text maintained by the application for safe multilingual FULLTEXT matching');
            $table->fullText('search_text', 'hv_p41_service_search_ft');
        });
        Schema::table('hongvan_post_translations', function (Blueprint $table): void {
            $table->text('search_text')->nullable()->comment('Unicode-normalized accent-folded text maintained by the application for safe multilingual FULLTEXT matching');
            $table->fullText('search_text', 'hv_p41_post_search_ft');
        });
        Schema::table('hongvan_project_translations', function (Blueprint $table): void {
            $table->text('search_text')->nullable()->comment('Unicode-normalized accent-folded text maintained by the application for safe multilingual FULLTEXT matching');
            $table->fullText('search_text', 'hv_p41_project_search_ft');
        });

        $sources = [
            'hongvan_product_translations' => ['name', 'short_description'],
            'hongvan_crop_solution_translations' => ['title', 'summary'],
            'hongvan_service_translations' => ['name', 'summary'],
            'hongvan_post_translations' => ['title', 'excerpt'],
            'hongvan_project_translations' => ['title', 'summary'],
        ];
        foreach ($sources as $tableName => [$titleColumn, $summaryColumn]) {
            DB::table($tableName)->select(['id', $titleColumn, $summaryColumn])->orderBy('id')->chunkById(200, function ($rows) use ($tableName, $titleColumn, $summaryColumn): void {
                foreach ($rows as $row) {
                    $text = trim((string) $row->{$titleColumn}.' '.strip_tags((string) $row->{$summaryColumn}));
                    $decomposed = class_exists(Normalizer::class) ? (Normalizer::normalize($text, Normalizer::FORM_D) ?: $text) : $text;
                    $folded = preg_replace('/\p{Mn}+/u', '', $decomposed) ?? $decomposed;
                    DB::table($tableName)->where('id', $row->id)->update(['search_text' => mb_strtolower(str_replace(['đ', 'Đ'], ['d', 'D'], $folded))]);
                }
            });
        }

        Schema::create('hongvan_search_logs', function (Blueprint $table): void {
            $table->comment('Privacy-reduced public search analytics recorded only when search analytics is enabled');
            $table->id()->comment('Internal primary key of the search analytics event');
            $table->string('locale', 10)->index()->comment('Active locale used to perform the public search');
            $table->string('normalized_term', 160)->comment('Normalized search term after email and phone-like values have been redacted');
            $table->char('term_hash', 64)->index()->comment('SHA-256 hash used to aggregate identical redacted search terms without storing another copy');
            $table->json('types')->nullable()->comment('Allowlisted content type filters applied to the search or null when all types were searched');
            $table->unsignedInteger('results_count')->default(0)->comment('Total number of published matching results returned by the search');
            $table->char('visitor_hash', 64)->nullable()->index()->comment('Rotatable keyed hash of the requester network identity; raw IP and user agent are never stored');
            $table->timestamp('created_at')->useCurrent()->index()->comment('UTC time when the privacy-reduced search event was recorded');
            $table->index(['locale', 'created_at'], 'hv_p41_search_locale_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_search_logs');
        Schema::table('hongvan_project_translations', function (Blueprint $table): void {
            $table->dropFullText('hv_p41_project_search_ft');
            $table->dropColumn('search_text');
        });
        Schema::table('hongvan_post_translations', function (Blueprint $table): void {
            $table->dropFullText('hv_p41_post_search_ft');
            $table->dropColumn('search_text');
        });
        Schema::table('hongvan_service_translations', function (Blueprint $table): void {
            $table->dropFullText('hv_p41_service_search_ft');
            $table->dropColumn('search_text');
        });
        Schema::table('hongvan_crop_solution_translations', function (Blueprint $table): void {
            $table->dropFullText('hv_p41_crop_solution_search_ft');
            $table->dropColumn('search_text');
        });
        Schema::table('hongvan_product_translations', function (Blueprint $table): void {
            $table->dropFullText('hv_p41_product_search_ft');
            $table->dropColumn('search_text');
        });
    }
};
