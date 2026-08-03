<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_galleries', function (Blueprint $table): void {
            $table->comment('Curated media galleries used to present verified company activities and capabilities');
            $table->id()->comment('Internal primary key of the gallery');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the gallery safely in APIs');
            $table->string('code', 100)->unique()->comment('Stable administrator-defined gallery code');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial state: draft, published, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the gallery is eligible for featured placements');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the gallery');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the gallery became publicly visible');
            $this->actors($table, 'hv_scg');
            $table->softDeletes()->comment('UTC time when the gallery was moved to trash');
            $this->timestamps($table, 'gallery');
        });

        Schema::create('hongvan_gallery_translations', function (Blueprint $table): void {
            $table->comment('Localized names, slugs, descriptions and SEO fields for galleries');
            $table->id()->comment('Internal primary key of the gallery translation');
            $table->foreignId('gallery_id')->comment('Gallery that owns this localized content')->constrained('hongvan_galleries', 'id', 'hv_scgt_gallery_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Localized gallery name');
            $table->string('slug', 191)->comment('Localized gallery URL slug');
            $table->text('description')->nullable()->comment('Optional localized gallery introduction');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description');
            $this->timestamps($table, 'gallery translation');
            $table->unique(['gallery_id', 'locale'], 'hv_scgt_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_scgt_slug_uniq');
        });

        Schema::create('hongvan_gallery_items', function (Blueprint $table): void {
            $table->comment('Ordered media items belonging to curated galleries');
            $table->id()->comment('Internal primary key of the gallery item');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the gallery item safely in APIs');
            $table->foreignId('gallery_id')->index()->comment('Gallery containing this media item')->constrained('hongvan_galleries', 'id', 'hv_scgi_gallery_fk')->cascadeOnDelete();
            $table->foreignId('media_id')->comment('Ready image or video selected from the media library')->constrained('hongvan_media', 'id', 'hv_scgi_media_fk')->restrictOnDelete();
            $table->string('status', 24)->default('draft')->index()->comment('Editorial state: draft, published, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether this media item is the gallery highlight');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order inside the gallery');
            $this->actors($table, 'hv_scgi');
            $table->softDeletes()->comment('UTC time when the gallery item was moved to trash');
            $this->timestamps($table, 'gallery item');
            $table->unique(['gallery_id', 'media_id'], 'hv_scgi_media_uniq');
        });

        Schema::create('hongvan_gallery_item_translations', function (Blueprint $table): void {
            $table->comment('Localized titles, captions and accessible alternative text for gallery media');
            $table->id()->comment('Internal primary key of the gallery item translation');
            $table->foreignId('gallery_item_id')->comment('Gallery item that owns this localized content')->constrained('hongvan_gallery_items', 'id', 'hv_scgit_item_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('title')->nullable()->comment('Optional localized title of the media item');
            $table->text('caption')->nullable()->comment('Optional localized image or video caption');
            $table->string('alt_text')->comment('Localized accessible alternative text describing the media');
            $this->timestamps($table, 'gallery item translation');
            $table->unique(['gallery_item_id', 'locale'], 'hv_scgit_locale_uniq');
        });

        Schema::create('hongvan_partners', function (Blueprint $table): void {
            $table->comment('Verified company partners managed without fabricated seed data');
            $table->id()->comment('Internal primary key of the partner');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the partner safely in APIs');
            $table->string('code', 100)->unique()->comment('Stable administrator-defined partner code');
            $table->foreignId('logo_media_id')->nullable()->comment('Optional partner logo selected from the media library')->constrained('hongvan_media', 'id', 'hv_scp_logo_fk')->restrictOnDelete();
            $table->string('website_url', 2048)->nullable()->comment('Optional verified external website URL of the partner');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial state: draft, published, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the partner is eligible for featured placements');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the partner');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the partner became publicly visible');
            $this->actors($table, 'hv_scp');
            $table->softDeletes()->comment('UTC time when the partner was moved to trash');
            $this->timestamps($table, 'partner');
        });

        Schema::create('hongvan_partner_translations', function (Blueprint $table): void {
            $table->comment('Localized partner names, descriptions and accessible logo text');
            $table->id()->comment('Internal primary key of the partner translation');
            $table->foreignId('partner_id')->comment('Partner that owns this localized content')->constrained('hongvan_partners', 'id', 'hv_scpt_partner_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Verified localized partner name');
            $table->text('description')->nullable()->comment('Optional localized description of the partnership');
            $table->string('logo_alt')->comment('Localized accessible alternative text for the partner logo');
            $this->timestamps($table, 'partner translation');
            $table->unique(['partner_id', 'locale'], 'hv_scpt_locale_uniq');
        });

        Schema::create('hongvan_certifications', function (Blueprint $table): void {
            $table->comment('Verified certifications with controlled image and document download visibility');
            $table->id()->comment('Internal primary key of the certification');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the certification safely in APIs');
            $table->string('code', 100)->unique()->comment('Stable administrator-defined certification code');
            $table->foreignId('image_media_id')->nullable()->comment('Optional certificate preview image from the media library')->constrained('hongvan_media', 'id', 'hv_scc_image_fk')->restrictOnDelete();
            $table->foreignId('document_media_id')->nullable()->comment('Optional certificate document from the media library')->constrained('hongvan_media', 'id', 'hv_scc_document_fk')->restrictOnDelete();
            $table->string('document_visibility', 16)->default('private')->index()->comment('Download policy: private or public');
            $table->date('issued_on')->nullable()->comment('Verified date on which the certification was issued');
            $table->date('expires_on')->nullable()->index()->comment('Optional verified expiry date of the certification');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial state: draft, published, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the certification is eligible for featured placements');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the certification');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the certification became publicly visible');
            $this->actors($table, 'hv_scc');
            $table->softDeletes()->comment('UTC time when the certification was moved to trash');
            $this->timestamps($table, 'certification');
        });

        Schema::create('hongvan_certification_translations', function (Blueprint $table): void {
            $table->comment('Localized certification names, issuers, descriptions, slugs and media labels');
            $table->id()->comment('Internal primary key of the certification translation');
            $table->foreignId('certification_id')->comment('Certification that owns this localized content')->constrained('hongvan_certifications', 'id', 'hv_scct_cert_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Verified localized certification name');
            $table->string('slug', 191)->comment('Localized certification URL slug');
            $table->string('issuer')->nullable()->comment('Optional verified localized issuing organization');
            $table->text('description')->nullable()->comment('Optional localized certification description');
            $table->string('image_alt')->nullable()->comment('Localized accessible alternative text for the certificate image');
            $table->string('document_label')->nullable()->comment('Localized public label for the certificate document download');
            $this->timestamps($table, 'certification translation');
            $table->unique(['certification_id', 'locale'], 'hv_scct_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_scct_slug_uniq');
        });

        Schema::create('hongvan_projects', function (Blueprint $table): void {
            $table->comment('Verified projects and case studies demonstrating company capabilities');
            $table->id()->comment('Internal primary key of the project');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the project safely in APIs');
            $table->string('code', 100)->unique()->comment('Stable administrator-defined project code');
            $table->date('started_on')->nullable()->comment('Optional verified project start date');
            $table->date('completed_on')->nullable()->index()->comment('Optional verified project completion date');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial state: draft, published, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the project is eligible for featured placements');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the project');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the project became publicly visible');
            $this->actors($table, 'hv_scpr');
            $table->softDeletes()->comment('UTC time when the project was moved to trash');
            $this->timestamps($table, 'project');
        });

        Schema::create('hongvan_project_translations', function (Blueprint $table): void {
            $table->comment('Localized project titles, slugs, summaries, content, locations and SEO fields');
            $table->id()->comment('Internal primary key of the project translation');
            $table->foreignId('project_id')->comment('Project that owns this localized content')->constrained('hongvan_projects', 'id', 'hv_scprt_project_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('title')->comment('Verified localized project title');
            $table->string('slug', 191)->comment('Localized project URL slug');
            $table->text('summary')->nullable()->comment('Optional localized project summary');
            $table->longText('content')->nullable()->comment('Optional localized project case-study content');
            $table->string('location')->nullable()->comment('Optional verified localized project location');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description');
            $this->timestamps($table, 'project translation');
            $table->unique(['project_id', 'locale'], 'hv_scprt_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_scprt_slug_uniq');
        });

        Schema::create('hongvan_project_media', function (Blueprint $table): void {
            $table->comment('Ordered media assigned to verified projects and case studies');
            $table->id()->comment('Internal primary key of the project media assignment');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the media assignment safely in APIs');
            $table->foreignId('project_id')->index()->comment('Project receiving this media assignment')->constrained('hongvan_projects', 'id', 'hv_scprm_project_fk')->cascadeOnDelete();
            $table->foreignId('media_id')->comment('Ready media selected from the media library')->constrained('hongvan_media', 'id', 'hv_scprm_media_fk')->restrictOnDelete();
            $table->string('role', 24)->default('gallery')->index()->comment('Presentation role: cover, gallery, or document');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the media inside the project');
            $this->timestamps($table, 'project media assignment');
            $table->unique(['project_id', 'media_id'], 'hv_scprm_media_uniq');
        });

        Schema::create('hongvan_project_media_translations', function (Blueprint $table): void {
            $table->comment('Localized captions and accessible alternative text for project media');
            $table->id()->comment('Internal primary key of the project media translation');
            $table->foreignId('project_media_id')->comment('Project media assignment that owns this localized content')->constrained('hongvan_project_media', 'id', 'hv_scprmt_media_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('alt_text')->comment('Localized accessible alternative text for project media');
            $table->text('caption')->nullable()->comment('Optional localized project media caption');
            $this->timestamps($table, 'project media translation');
            $table->unique(['project_media_id', 'locale'], 'hv_scprmt_locale_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_project_media_translations');
        Schema::dropIfExists('hongvan_project_media');
        Schema::dropIfExists('hongvan_project_translations');
        Schema::dropIfExists('hongvan_projects');
        Schema::dropIfExists('hongvan_certification_translations');
        Schema::dropIfExists('hongvan_certifications');
        Schema::dropIfExists('hongvan_partner_translations');
        Schema::dropIfExists('hongvan_partners');
        Schema::dropIfExists('hongvan_gallery_item_translations');
        Schema::dropIfExists('hongvan_gallery_items');
        Schema::dropIfExists('hongvan_gallery_translations');
        Schema::dropIfExists('hongvan_galleries');
    }

    private function actors(Blueprint $table, string $prefix): void
    {
        $table->foreignId('created_by')->nullable()->comment('Administrator who created the record')->constrained('hongvan_users', 'id', $prefix.'_created_fk')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the record')->constrained('hongvan_users', 'id', $prefix.'_updated_fk')->nullOnDelete();
        $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the record to trash')->constrained('hongvan_users', 'id', $prefix.'_deleted_fk')->nullOnDelete();
    }

    private function timestamps(Blueprint $table, string $record): void
    {
        $table->timestamp('created_at')->nullable()->comment("UTC time when the {$record} was created");
        $table->timestamp('updated_at')->nullable()->comment("UTC time when the {$record} was most recently updated");
    }
};
