<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_service_categories', function (Blueprint $table): void {
            $table->comment('Hierarchical categories for general company services without owning transportation or warehouse domain data');
            $table->id()->comment('Internal primary key of the service category');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the service category in APIs');
            $table->foreignId('parent_id')->nullable()->comment('Optional parent category used to build the service category hierarchy')->constrained('hongvan_service_categories', 'id', 'hv_sc_parent_fk')->nullOnDelete();
            $table->string('code', 64)->unique()->comment('Stable administrator-defined code of the service category');
            $table->boolean('is_active')->default(true)->index()->comment('Whether the category is available for service assignment and public preparation');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order among sibling service categories');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the service category')->constrained('hongvan_users', 'id', 'hv_sc_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the service category')->constrained('hongvan_users', 'id', 'hv_sc_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the service category to trash')->constrained('hongvan_users', 'id', 'hv_sc_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the service category was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the service category was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the service category was most recently updated');
            $table->index(['parent_id', 'is_active', 'sort_order'], 'hv_sc_tree_idx');
        });

        Schema::create('hongvan_service_category_translations', function (Blueprint $table): void {
            $table->comment('Localized service category names, slugs and SEO preparation fields');
            $table->id()->comment('Internal primary key of the service category translation');
            $table->foreignId('service_category_id')->comment('Service category that owns this localized content')->constrained('hongvan_service_categories', 'id', 'hv_sct_category_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public service category name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the service category namespace');
            $table->text('summary')->nullable()->comment('Optional localized introduction for category cards and listings');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['service_category_id', 'locale'], 'hv_sct_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_sct_slug_uniq');
        });

        Schema::create('hongvan_services', function (Blueprint $table): void {
            $table->comment('General company service entries and explicit links to specialized transportation or warehouse modules');
            $table->id()->comment('Internal primary key of the service');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the service in APIs and future form source references');
            $table->foreignId('service_category_id')->nullable()->comment('Optional category used to organize this service')->constrained('hongvan_service_categories', 'id', 'hv_services_category_fk')->nullOnDelete();
            $table->string('code', 100)->unique()->comment('Stable administrator-defined service code');
            $table->string('service_type', 32)->default('general')->index()->comment('Ownership boundary: general, transportation_link, or warehouse_link');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial status: draft, published, scheduled, or archived');
            $table->string('cta_type', 16)->default('contact')->index()->comment('Future public form action: none, contact, or quote');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the service is eligible for featured placements');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the service');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the service becomes publicly visible');
            $table->timestamp('unpublished_at')->nullable()->index()->comment('Optional UTC time when public visibility ends');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the service')->constrained('hongvan_users', 'id', 'hv_services_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the service')->constrained('hongvan_users', 'id', 'hv_services_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the service to trash')->constrained('hongvan_users', 'id', 'hv_services_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the service was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the service was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the service was most recently updated');
            $table->index(['service_category_id', 'status', 'sort_order'], 'hv_services_catalog_idx');
            $table->index(['status', 'published_at', 'unpublished_at'], 'hv_services_publish_idx');
        });

        Schema::create('hongvan_service_translations', function (Blueprint $table): void {
            $table->comment('Localized service content, CTA labels, slugs and SEO preparation fields');
            $table->id()->comment('Internal primary key of the service translation');
            $table->foreignId('service_id')->comment('Service that owns this localized content')->constrained('hongvan_services', 'id', 'hv_st_service_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public service name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the service namespace');
            $table->text('summary')->nullable()->comment('Optional localized summary for cards and search results');
            $table->longText('content')->nullable()->comment('Optional sanitized localized general service content');
            $table->json('content_sections')->nullable()->comment('Ordered allowlisted localized section objects containing plain title and body text');
            $table->string('cta_label')->nullable()->comment('Optional localized label for the configured contact or quote action');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['service_id', 'locale'], 'hv_st_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_st_slug_uniq');
        });

        Schema::create('hongvan_service_media', function (Blueprint $table): void {
            $table->comment('Ordered media library assets assigned to general service entries');
            $table->foreignId('service_id')->comment('Service that owns the media assignment')->constrained('hongvan_services', 'id', 'hv_sm_service_fk')->cascadeOnDelete();
            $table->foreignId('media_id')->comment('Media library asset assigned to the service')->constrained('hongvan_media', 'id', 'hv_sm_media_fk')->cascadeOnDelete();
            $table->string('role', 24)->default('gallery')->comment('Presentation role of the asset: hero, gallery, or document');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending display order inside the service media collection');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the media assignment was created');
            $table->primary(['service_id', 'media_id']);
            $table->index(['service_id', 'role', 'sort_order'], 'hv_sm_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_service_media');
        Schema::dropIfExists('hongvan_service_translations');
        Schema::dropIfExists('hongvan_services');
        Schema::dropIfExists('hongvan_service_category_translations');
        Schema::dropIfExists('hongvan_service_categories');
    }
};
