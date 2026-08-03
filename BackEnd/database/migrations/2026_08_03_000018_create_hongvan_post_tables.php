<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_post_categories', function (Blueprint $table): void {
            $table->comment('Hierarchical editorial categories used to organize news and knowledge posts');
            $table->id()->comment('Internal primary key of the post category');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the category safely in APIs');
            $table->foreignId('parent_id')->nullable()->comment('Optional parent category used to build the editorial hierarchy')->constrained('hongvan_post_categories', 'id', 'hv_pstc_parent_fk')->nullOnDelete();
            $table->string('code', 64)->unique()->comment('Stable administrator-defined category code');
            $table->boolean('is_active')->default(true)->index()->comment('Whether the category may be selected and exposed by public data sources');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order among sibling categories');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the category')->constrained('hongvan_users', 'id', 'hv_pstc_created_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the category')->constrained('hongvan_users', 'id', 'hv_pstc_updated_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the category to trash')->constrained('hongvan_users', 'id', 'hv_pstc_deleted_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the category was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the category was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the category was most recently updated');
            $table->index(['parent_id', 'is_active', 'sort_order'], 'hv_pstc_tree_idx');
        });

        Schema::create('hongvan_post_category_translations', function (Blueprint $table): void {
            $table->comment('Localized category names, slugs, descriptions and SEO preparation fields');
            $table->id()->comment('Internal primary key of the category translation');
            $table->foreignId('post_category_id')->comment('Category that owns this localized content')->constrained('hongvan_post_categories', 'id', 'hv_pstct_category_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public category name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the post category namespace');
            $table->text('description')->nullable()->comment('Optional localized category introduction');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['post_category_id', 'locale'], 'hv_pstct_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_pstct_slug_uniq');
        });

        Schema::create('hongvan_post_tags', function (Blueprint $table): void {
            $table->comment('Reusable editorial tags assigned to news and knowledge posts');
            $table->id()->comment('Internal primary key of the post tag');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the tag safely in APIs');
            $table->string('code', 64)->unique()->comment('Stable administrator-defined tag code');
            $table->boolean('is_active')->default(true)->index()->comment('Whether the tag may be assigned and exposed by public data sources');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the tag');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the tag')->constrained('hongvan_users', 'id', 'hv_pstg_created_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the tag')->constrained('hongvan_users', 'id', 'hv_pstg_updated_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the tag to trash')->constrained('hongvan_users', 'id', 'hv_pstg_deleted_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the tag was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the tag was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the tag was most recently updated');
        });

        Schema::create('hongvan_post_tag_translations', function (Blueprint $table): void {
            $table->comment('Localized post tag names and slugs');
            $table->id()->comment('Internal primary key of the tag translation');
            $table->foreignId('post_tag_id')->comment('Tag that owns this localized content')->constrained('hongvan_post_tags', 'id', 'hv_pstgt_tag_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public tag name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the post tag namespace');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['post_tag_id', 'locale'], 'hv_pstgt_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_pstgt_slug_uniq');
        });

        Schema::create('hongvan_posts', function (Blueprint $table): void {
            $table->comment('Editorial news and knowledge posts with authorship and controlled publication lifecycle');
            $table->id()->comment('Internal primary key of the post');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the post safely in APIs and data sources');
            $table->string('code', 100)->unique()->comment('Stable administrator-defined post code');
            $table->foreignId('post_category_id')->nullable()->index()->comment('Optional primary editorial category')->constrained('hongvan_post_categories', 'id', 'hv_pst_category_fk')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->index()->comment('Administrator credited as the post author')->constrained('hongvan_users', 'id', 'hv_pst_author_fk')->nullOnDelete();
            $table->foreignId('featured_media_id')->nullable()->comment('Optional ready image from the media library used as the featured image')->constrained('hongvan_media', 'id', 'hv_pst_media_fk')->nullOnDelete();
            $table->string('status', 24)->default('draft')->index()->comment('Editorial state: draft, scheduled, published, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the post is eligible for featured public placements');
            $table->timestamp('scheduled_for')->nullable()->index()->comment('UTC time when a scheduled post becomes eligible for publication');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the post actually became publicly visible');
            $table->timestamp('unpublished_at')->nullable()->index()->comment('Optional UTC time when public visibility ends');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the post')->constrained('hongvan_users', 'id', 'hv_pst_created_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the post')->constrained('hongvan_users', 'id', 'hv_pst_updated_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the post to trash')->constrained('hongvan_users', 'id', 'hv_pst_deleted_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the post was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the post was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the post was most recently updated');
            $table->index(['status', 'scheduled_for', 'published_at'], 'hv_pst_publish_idx');
            $table->index(['post_category_id', 'status', 'published_at'], 'hv_pst_category_idx');
        });

        Schema::create('hongvan_post_translations', function (Blueprint $table): void {
            $table->comment('Localized sanitized post content, slugs, excerpts and SEO preparation fields');
            $table->id()->comment('Internal primary key of the post translation');
            $table->foreignId('post_id')->comment('Post that owns this localized content')->constrained('hongvan_posts', 'id', 'hv_psttr_post_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('title')->comment('Localized public post title');
            $table->string('slug', 191)->comment('Current localized URL slug unique inside the post namespace');
            $table->text('excerpt')->nullable()->comment('Optional localized summary for cards, feeds and search results');
            $table->longText('content_html')->comment('Sanitized allowlisted localized rich text HTML');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['post_id', 'locale'], 'hv_psttr_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_psttr_slug_uniq');
            $table->fullText(['title', 'excerpt'], 'hv_psttr_search_fulltext');
        });

        Schema::create('hongvan_post_tag_links', function (Blueprint $table): void {
            $table->comment('Many-to-many assignments between posts and editorial tags');
            $table->foreignId('post_id')->comment('Post receiving the tag assignment')->constrained('hongvan_posts', 'id', 'hv_psttl_post_fk')->cascadeOnDelete();
            $table->foreignId('post_tag_id')->comment('Tag assigned to the post')->constrained('hongvan_post_tags', 'id', 'hv_psttl_tag_fk')->restrictOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the tag was assigned to the post');
            $table->primary(['post_id', 'post_tag_id'], 'hv_psttl_primary');
        });

        Schema::create('hongvan_post_slug_histories', function (Blueprint $table): void {
            $table->comment('Historical localized post slugs reserved for safe future canonical redirects');
            $table->id()->comment('Internal primary key of the slug history entry');
            $table->foreignId('post_id')->index()->comment('Post that previously owned the localized slug')->constrained('hongvan_posts', 'id', 'hv_pstsh_post_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale in which the historical slug was used');
            $table->string('slug', 191)->comment('Previous localized slug that must redirect to the current canonical slug');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the previous slug was recorded');
            $table->unique(['locale', 'slug'], 'hv_pstsh_slug_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_post_slug_histories');
        Schema::dropIfExists('hongvan_post_tag_links');
        Schema::dropIfExists('hongvan_post_translations');
        Schema::dropIfExists('hongvan_posts');
        Schema::dropIfExists('hongvan_post_tag_translations');
        Schema::dropIfExists('hongvan_post_tags');
        Schema::dropIfExists('hongvan_post_category_translations');
        Schema::dropIfExists('hongvan_post_categories');
    }
};
