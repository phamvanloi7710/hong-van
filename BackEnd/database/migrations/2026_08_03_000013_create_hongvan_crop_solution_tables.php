<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_crop_categories', function (Blueprint $table): void {
            $table->comment('Hierarchical crop groups used to organize agricultural solution content');
            $table->id()->comment('Internal primary key of the crop category');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the crop category in APIs');
            $table->foreignId('parent_id')->nullable()->comment('Optional parent crop category in the hierarchy')->constrained('hongvan_crop_categories', 'id', 'hv_cc_parent_fk')->nullOnDelete();
            $table->string('code', 64)->unique()->comment('Stable administrator-defined crop category code');
            $table->foreignId('image_media_id')->nullable()->comment('Optional media library image representing the crop category')->constrained('hongvan_media', 'id', 'hv_cc_image_fk')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index()->comment('Whether the category is available for crop assignment and public preparation');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order among sibling crop categories');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the crop category')->constrained('hongvan_users', 'id', 'hv_cc_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the crop category')->constrained('hongvan_users', 'id', 'hv_cc_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the crop category to trash')->constrained('hongvan_users', 'id', 'hv_cc_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the crop category was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the crop category was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the crop category was most recently updated');
            $table->index(['parent_id', 'is_active', 'sort_order'], 'hv_cc_tree_idx');
        });

        Schema::create('hongvan_crop_category_translations', function (Blueprint $table): void {
            $table->comment('Localized names, slugs and SEO preparation fields for crop categories');
            $table->id()->comment('Internal primary key of the crop category translation');
            $table->foreignId('crop_category_id')->comment('Crop category that owns this localized content')->constrained('hongvan_crop_categories', 'id', 'hv_cct_category_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public crop category name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the crop category namespace');
            $table->text('summary')->nullable()->comment('Optional localized crop category introduction');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['crop_category_id', 'locale'], 'hv_cct_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_cct_slug_uniq');
        });

        Schema::create('hongvan_crops', function (Blueprint $table): void {
            $table->comment('Administrator-managed crop profiles that own growth stages and nutrition solutions');
            $table->id()->comment('Internal primary key of the crop');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the crop in APIs');
            $table->foreignId('crop_category_id')->nullable()->comment('Optional crop category used to organize the crop profile')->constrained('hongvan_crop_categories', 'id', 'hv_crops_category_fk')->nullOnDelete();
            $table->string('code', 64)->unique()->comment('Stable administrator-defined crop code');
            $table->foreignId('image_media_id')->nullable()->comment('Optional media library image representing the crop')->constrained('hongvan_media', 'id', 'hv_crops_image_fk')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index()->comment('Whether the crop is active for administration and public preparation');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending crop display order');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the crop')->constrained('hongvan_users', 'id', 'hv_crops_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the crop')->constrained('hongvan_users', 'id', 'hv_crops_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the crop to trash')->constrained('hongvan_users', 'id', 'hv_crops_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the crop was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the crop was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the crop was most recently updated');
            $table->index(['crop_category_id', 'is_active', 'sort_order'], 'hv_crops_catalog_idx');
        });

        Schema::create('hongvan_crop_translations', function (Blueprint $table): void {
            $table->comment('Localized crop profile content and SEO preparation fields');
            $table->id()->comment('Internal primary key of the crop translation');
            $table->foreignId('crop_id')->comment('Crop that owns this localized content')->constrained('hongvan_crops', 'id', 'hv_ct_crop_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public crop name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the crop namespace');
            $table->text('summary')->nullable()->comment('Optional localized crop summary for cards and listings');
            $table->longText('description')->nullable()->comment('Optional sanitized localized crop profile description');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['crop_id', 'locale'], 'hv_ct_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_ct_slug_uniq');
        });

        Schema::create('hongvan_crop_stages', function (Blueprint $table): void {
            $table->comment('Ordered growth stages belonging to a crop and used by stage-specific solutions');
            $table->id()->comment('Internal primary key of the crop stage');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the crop stage in APIs');
            $table->foreignId('crop_id')->comment('Crop that owns this ordered growth stage')->constrained('hongvan_crops', 'id', 'hv_cs_crop_fk')->cascadeOnDelete();
            $table->string('code', 64)->comment('Stable administrator-defined stage code unique inside the crop');
            $table->foreignId('image_media_id')->nullable()->comment('Optional media library image illustrating the crop stage')->constrained('hongvan_media', 'id', 'hv_cs_image_fk')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index()->comment('Whether the stage is available for solution assignment and public preparation');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending timeline position of the stage inside its crop');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the crop stage')->constrained('hongvan_users', 'id', 'hv_cs_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the crop stage')->constrained('hongvan_users', 'id', 'hv_cs_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the crop stage to trash')->constrained('hongvan_users', 'id', 'hv_cs_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the crop stage was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the crop stage was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the crop stage was most recently updated');
            $table->unique(['crop_id', 'code'], 'hv_cs_crop_code_uniq');
            $table->index(['crop_id', 'is_active', 'sort_order'], 'hv_cs_timeline_idx');
        });

        Schema::create('hongvan_crop_stage_translations', function (Blueprint $table): void {
            $table->comment('Localized names and stage-specific guidance for crop timelines');
            $table->id()->comment('Internal primary key of the crop stage translation');
            $table->foreignId('crop_stage_id')->comment('Crop stage that owns this localized content')->constrained('hongvan_crop_stages', 'id', 'hv_cst_stage_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public crop stage name');
            $table->text('summary')->nullable()->comment('Optional localized stage summary for the timeline');
            $table->longText('content')->nullable()->comment('Optional sanitized stage guidance without unverified absolute recommendations');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['crop_stage_id', 'locale'], 'hv_cst_locale_uniq');
        });

        Schema::create('hongvan_crop_solutions', function (Blueprint $table): void {
            $table->comment('Editorial crop nutrition solutions linked to one crop stage and recommended catalog products');
            $table->id()->comment('Internal primary key of the crop solution');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the crop solution in APIs');
            $table->foreignId('crop_id')->comment('Crop that owns this solution')->constrained('hongvan_crops', 'id', 'hv_sol_crop_fk')->cascadeOnDelete();
            $table->foreignId('crop_stage_id')->nullable()->comment('Optional crop stage targeted by this solution')->constrained('hongvan_crop_stages', 'id', 'hv_sol_stage_fk')->nullOnDelete();
            $table->string('code', 100)->unique()->comment('Stable administrator-defined crop solution code');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial status: draft, published, scheduled, or archived');
            $table->foreignId('hero_media_id')->nullable()->comment('Optional media library hero image assigned to the solution')->constrained('hongvan_media', 'id', 'hv_sol_hero_fk')->nullOnDelete();
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the solution is eligible for featured placements');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending solution display order inside its crop stage');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the solution becomes publicly visible');
            $table->timestamp('unpublished_at')->nullable()->index()->comment('Optional UTC time when public visibility ends');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the crop solution')->constrained('hongvan_users', 'id', 'hv_sol_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the crop solution')->constrained('hongvan_users', 'id', 'hv_sol_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the crop solution to trash')->constrained('hongvan_users', 'id', 'hv_sol_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the crop solution was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the crop solution was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the crop solution was most recently updated');
            $table->index(['crop_id', 'crop_stage_id', 'status', 'sort_order'], 'hv_sol_catalog_idx');
            $table->index(['status', 'published_at', 'unpublished_at'], 'hv_sol_publish_idx');
        });

        Schema::create('hongvan_crop_solution_translations', function (Blueprint $table): void {
            $table->comment('Localized crop solution content sections, slugs and SEO preparation fields');
            $table->id()->comment('Internal primary key of the crop solution translation');
            $table->foreignId('crop_solution_id')->comment('Crop solution that owns this localized content')->constrained('hongvan_crop_solutions', 'id', 'hv_solt_solution_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('title')->comment('Localized public crop solution title');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the crop solution namespace');
            $table->text('summary')->nullable()->comment('Optional localized solution summary for cards and search results');
            $table->longText('content')->nullable()->comment('Optional sanitized localized editorial solution introduction');
            $table->json('content_sections')->nullable()->comment('Ordered allowlisted localized section objects containing plain title and body text');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['crop_solution_id', 'locale'], 'hv_solt_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_solt_slug_uniq');
        });

        Schema::create('hongvan_crop_solution_products', function (Blueprint $table): void {
            $table->comment('Ordered internal links from crop solutions to recommended catalog products');
            $table->foreignId('crop_solution_id')->comment('Crop solution that recommends the catalog product')->constrained('hongvan_crop_solutions', 'id', 'hv_solp_solution_fk')->cascadeOnDelete();
            $table->foreignId('product_id')->comment('Catalog product recommended by the crop solution')->constrained('hongvan_products', 'id', 'hv_solp_product_fk')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending recommended product display order inside the solution');
            $table->string('recommendation_note')->nullable()->comment('Optional administrator note explaining the non-absolute recommendation context');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the solution-product link was created');
            $table->primary(['crop_solution_id', 'product_id']);
            $table->index(['crop_solution_id', 'sort_order'], 'hv_solp_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_crop_solution_products');
        Schema::dropIfExists('hongvan_crop_solution_translations');
        Schema::dropIfExists('hongvan_crop_solutions');
        Schema::dropIfExists('hongvan_crop_stage_translations');
        Schema::dropIfExists('hongvan_crop_stages');
        Schema::dropIfExists('hongvan_crop_translations');
        Schema::dropIfExists('hongvan_crops');
        Schema::dropIfExists('hongvan_crop_category_translations');
        Schema::dropIfExists('hongvan_crop_categories');
    }
};
