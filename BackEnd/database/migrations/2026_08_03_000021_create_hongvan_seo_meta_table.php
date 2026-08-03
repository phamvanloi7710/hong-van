<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_seo_meta', function (Blueprint $table): void {
            $table->comment('Locale-specific SEO, canonical and social-sharing metadata for allowlisted public content entities');
            $table->id()->comment('Internal primary key used only by the application and foreign keys');
            $table->ulid('public_id')->unique()->comment('Stable public ULID exposed by the admin API');
            $table->string('seoable_type', 50)->comment('Stable allowlisted content type such as product, post or project; never a PHP class name');
            $table->unsignedBigInteger('seoable_id')->comment('Internal identifier of the content entity selected by seoable_type');
            $table->string('locale', 10)->comment('Locale this metadata applies to, currently vi, en or zh');
            $table->string('meta_title', 255)->nullable()->comment('Search result title override; falls back to entity, page and global defaults when empty');
            $table->text('meta_description')->nullable()->comment('Search result description override; falls back to entity, page and global defaults when empty');
            $table->string('canonical_url', 2048)->nullable()->comment('Optional absolute HTTP or HTTPS canonical override validated by the server');
            $table->boolean('robots_index')->default(true)->comment('Whether a published public page may be indexed; draft, preview and admin contexts always override this to false');
            $table->boolean('robots_follow')->default(true)->comment('Whether crawlers may follow links from a published public page');
            $table->string('og_title', 255)->nullable()->comment('Open Graph title override; falls back to the resolved meta title');
            $table->text('og_description')->nullable()->comment('Open Graph description override; falls back to the resolved meta description');
            $table->foreignId('og_image_media_id')->nullable()->comment('Ready public image used for Open Graph and Twitter variants')->constrained('hongvan_media')->restrictOnDelete();
            $table->string('og_type', 30)->default('website')->comment('Allowlisted Open Graph object type: website, article or product');
            $table->string('twitter_card', 40)->default('summary_large_image')->comment('Allowlisted Twitter card style: summary or summary_large_image');
            $table->string('twitter_title', 255)->nullable()->comment('Twitter title override; falls back to the resolved Open Graph title');
            $table->text('twitter_description')->nullable()->comment('Twitter description override; falls back to the resolved Open Graph description');
            $table->json('focus_keywords')->nullable()->comment('Editorial keyword hints for administrators; never rendered as a meta keywords tag');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the metadata record')->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently updated the metadata record')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the metadata record was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the metadata record was most recently updated');

            $table->unique(['seoable_type', 'seoable_id', 'locale'], 'hv_p42_seo_entity_locale_uq');
            $table->index(['seoable_type', 'seoable_id'], 'hv_p42_seo_entity_idx');
            $table->index(['locale', 'robots_index'], 'hv_p42_seo_locale_index_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_seo_meta');
    }
};
