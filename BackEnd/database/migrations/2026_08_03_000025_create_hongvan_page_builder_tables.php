<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_pages', function (Blueprint $table): void {
            $table->comment('Page Builder page identities and lifecycle pointers for public website pages');
            $table->id()->comment('Internal primary key of the page');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the page safely');
            $table->string('code', 100)->unique()->comment('Stable machine code of the page independent from localized slugs');
            $table->string('type', 32)->default('standard')->index()->comment('Page classification: standard, landing, or system');
            $table->string('status', 24)->default('draft')->index()->comment('Page lifecycle state: draft, published, or archived');
            $table->boolean('is_home')->default(false)->index()->comment('Whether this page is the website home page');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the page')->constrained('hongvan_users', 'id', 'hv_pages_creator_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who last changed page metadata or draft')->constrained('hongvan_users', 'id', 'hv_pages_updater_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who soft-deleted the page')->constrained('hongvan_users', 'id', 'hv_pages_deleter_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the page was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the page was last changed');
            $table->softDeletes()->comment('UTC time when the page was soft-deleted');
        });

        Schema::create('hongvan_page_translations', function (Blueprint $table): void {
            $table->comment('Localized Page Builder page titles, navigation labels, and public slugs');
            $table->id()->comment('Internal primary key of the page translation');
            $table->foreignId('page_id')->comment('Page that owns this localized metadata')->constrained('hongvan_pages', 'id', 'hv_page_translations_page_fk')->cascadeOnDelete();
            $table->string('locale', 12)->index()->comment('Locale code of this translation such as vi, en, or zh');
            $table->string('title', 255)->comment('Localized page title shown to administrators and visitors');
            $table->string('navigation_label', 160)->nullable()->comment('Optional shorter localized title used by navigation menus');
            $table->string('slug', 191)->comment('Localized URL slug without locale prefix or leading slash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was last changed');
            $table->unique(['page_id', 'locale'], 'hv_page_translations_locale_unique');
            $table->unique(['locale', 'slug'], 'hv_page_translations_slug_unique');
        });

        Schema::create('hongvan_page_versions', function (Blueprint $table): void {
            $table->comment('Validated versioned PageDocument snapshots; published rows are immutable');
            $table->id()->comment('Internal primary key of the page version');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the page version safely');
            $table->foreignId('page_id')->comment('Page that owns this document version')->constrained('hongvan_pages', 'id', 'hv_page_versions_page_fk')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->comment('Monotonically increasing version number within the page');
            $table->string('status', 24)->index()->comment('Version lifecycle state: draft, published, archived, or superseded');
            $table->unsignedSmallInteger('schema_version')->comment('Server PageDocument schema version used by document_json');
            $table->json('document_json')->comment('Validated PageDocument JSON containing only allowlisted block types and values');
            $table->char('checksum', 64)->index()->comment('SHA-256 checksum of the canonical PageDocument for cache identity');
            $table->foreignId('parent_version_id')->nullable()->comment('Earlier version from which this version was cloned')->constrained('hongvan_page_versions', 'id', 'hv_page_versions_parent_fk')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->comment('Administrator who created or started this version')->constrained('hongvan_users', 'id', 'hv_page_versions_creator_fk')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->comment('Administrator who published this immutable version')->constrained('hongvan_users', 'id', 'hv_page_versions_publisher_fk')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when this version was published');
            $table->timestamp('created_at')->nullable()->comment('UTC time when this version was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when this editable draft was last changed');
            $table->unique(['page_id', 'version_number'], 'hv_page_versions_number_unique');
            $table->index(['page_id', 'status', 'version_number'], 'hv_page_versions_status_idx');
        });

        Schema::create('hongvan_page_publish_schedules', function (Blueprint $table): void {
            $table->comment('Idempotent scheduled publish and unpublish operations for Page Builder versions');
            $table->id()->comment('Internal primary key of the page publish schedule');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the schedule safely');
            $table->foreignId('page_id')->comment('Page affected by the scheduled operation')->constrained('hongvan_pages', 'id', 'hv_page_schedules_page_fk')->cascadeOnDelete();
            $table->foreignId('page_version_id')->nullable()->comment('Validated version to publish; null is allowed for unpublish operations')->constrained('hongvan_page_versions', 'id', 'hv_page_schedules_version_fk')->cascadeOnDelete();
            $table->string('action', 24)->comment('Scheduled operation: publish or unpublish');
            $table->string('status', 24)->default('pending')->index()->comment('Execution state: pending, processing, completed, cancelled, or failed');
            $table->timestamp('scheduled_at')->index()->comment('UTC time at which the operation becomes eligible');
            $table->timestamp('processed_at')->nullable()->comment('UTC time when the operation finished or failed');
            $table->text('failure_message')->nullable()->comment('Sanitized operational failure summary without secrets or stack trace');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the schedule')->constrained('hongvan_users', 'id', 'hv_page_schedules_creator_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the schedule was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when schedule state last changed');
            $table->index(['status', 'scheduled_at'], 'hv_page_schedules_due_idx');
        });

        Schema::create('hongvan_page_locks', function (Blueprint $table): void {
            $table->comment('Exclusive expiring edit locks used to coordinate Page Builder editors');
            $table->id()->comment('Internal primary key of the page edit lock');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the lock safely');
            $table->foreignId('page_id')->unique()->comment('Page currently protected by this exclusive lock')->constrained('hongvan_pages', 'id', 'hv_page_locks_page_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->comment('Administrator who owns the current edit lock')->constrained('hongvan_users', 'id', 'hv_page_locks_user_fk')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique()->comment('SHA-256 hash of the lock token; the raw token is never stored');
            $table->timestamp('expires_at')->index()->comment('UTC time after which another editor may acquire the lock');
            $table->timestamp('refreshed_at')->comment('UTC time when the lock heartbeat was last accepted');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the lock was acquired');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the lock record last changed');
        });

        Schema::create('hongvan_page_templates', function (Blueprint $table): void {
            $table->comment('Reusable Page Builder template identities with versioned validated documents');
            $table->id()->comment('Internal primary key of the page template');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the template safely');
            $table->string('key', 100)->unique()->comment('Stable machine key of the reusable page template');
            $table->string('name', 160)->comment('Administrator-facing template name');
            $table->text('description')->nullable()->comment('Administrator-facing explanation of the template purpose');
            $table->boolean('is_system')->default(false)->index()->comment('Whether the template is maintained by the application rather than ordinary editors');
            $table->boolean('is_active')->default(true)->index()->comment('Whether editors may select this template for new pages');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the template')->constrained('hongvan_users', 'id', 'hv_page_templates_creator_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who last changed template metadata')->constrained('hongvan_users', 'id', 'hv_page_templates_updater_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the template was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when template metadata last changed');
        });

        Schema::create('hongvan_page_template_versions', function (Blueprint $table): void {
            $table->comment('Validated immutable or draft PageDocument versions belonging to reusable page templates');
            $table->id()->comment('Internal primary key of the page template version');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the template version safely');
            $table->foreignId('page_template_id')->comment('Reusable template that owns this document version')->constrained('hongvan_page_templates', 'id', 'hv_page_template_versions_template_fk')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->comment('Monotonically increasing version number within the template');
            $table->string('status', 24)->index()->comment('Template version state: draft, published, archived, or superseded');
            $table->unsignedSmallInteger('schema_version')->comment('Server PageDocument schema version used by document_json');
            $table->json('document_json')->comment('Validated reusable PageDocument JSON with no executable code or view path');
            $table->char('checksum', 64)->index()->comment('SHA-256 checksum of the canonical template document');
            $table->foreignId('parent_version_id')->nullable()->comment('Earlier template version from which this version was cloned')->constrained('hongvan_page_template_versions', 'id', 'hv_page_template_versions_parent_fk')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->comment('Administrator who created this template version')->constrained('hongvan_users', 'id', 'hv_page_template_versions_creator_fk')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->comment('UTC time when this template version was published');
            $table->timestamp('created_at')->nullable()->comment('UTC time when this template version was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when this editable template draft last changed');
            $table->unique(['page_template_id', 'version_number'], 'hv_page_template_versions_number_unique');
        });

        Schema::create('hongvan_page_preview_sessions', function (Blueprint $table): void {
            $table->comment('Short-lived Page Builder preview session references with hashed tokens and ownership');
            $table->id()->comment('Internal primary key of the preview session');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the preview session safely');
            $table->foreignId('page_id')->comment('Page being previewed')->constrained('hongvan_pages', 'id', 'hv_page_previews_page_fk')->cascadeOnDelete();
            $table->foreignId('page_version_id')->nullable()->comment('Persisted draft version used as the preview baseline')->constrained('hongvan_page_versions', 'id', 'hv_page_previews_version_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->comment('Administrator who owns and may update the preview session')->constrained('hongvan_users', 'id', 'hv_page_previews_user_fk')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique()->comment('SHA-256 hash of the preview token; the raw signed token is never stored');
            $table->string('locale', 12)->comment('Locale rendered by the preview session');
            $table->timestamp('expires_at')->index()->comment('UTC time when the preview session becomes invalid');
            $table->timestamp('last_viewed_at')->nullable()->comment('UTC time when the preview was most recently rendered');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the preview session was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when preview session metadata last changed');
            $table->index(['user_id', 'expires_at'], 'hv_page_previews_owner_expiry_idx');
        });

        Schema::table('hongvan_page_templates', function (Blueprint $table): void {
            $table->unsignedBigInteger('published_version_id')->nullable()->after('is_active')->index()->comment('Immutable template version currently offered to editors');
            $table->foreign('published_version_id', 'hv_page_templates_published_fk')->references('id')->on('hongvan_page_template_versions')->nullOnDelete();
        });

        Schema::table('hongvan_pages', function (Blueprint $table): void {
            $table->unsignedBigInteger('page_template_id')->nullable()->after('is_home')->index()->comment('Reusable template identity from which this page originated');
            $table->unsignedBigInteger('draft_version_id')->nullable()->after('page_template_id')->index()->comment('Current mutable validated draft version of the page');
            $table->unsignedBigInteger('published_version_id')->nullable()->after('draft_version_id')->index()->comment('Immutable version currently rendered for public visitors');
            $table->foreign('page_template_id', 'hv_pages_template_fk')->references('id')->on('hongvan_page_templates')->nullOnDelete();
            $table->foreign('draft_version_id', 'hv_pages_draft_version_fk')->references('id')->on('hongvan_page_versions')->nullOnDelete();
            $table->foreign('published_version_id', 'hv_pages_published_version_fk')->references('id')->on('hongvan_page_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hongvan_pages', function (Blueprint $table): void {
            $table->dropForeign('hv_pages_template_fk');
            $table->dropForeign('hv_pages_draft_version_fk');
            $table->dropForeign('hv_pages_published_version_fk');
            $table->dropColumn(['page_template_id', 'draft_version_id', 'published_version_id']);
        });
        Schema::table('hongvan_page_templates', function (Blueprint $table): void {
            $table->dropForeign('hv_page_templates_published_fk');
            $table->dropColumn('published_version_id');
        });
        Schema::dropIfExists('hongvan_page_preview_sessions');
        Schema::dropIfExists('hongvan_page_template_versions');
        Schema::dropIfExists('hongvan_page_templates');
        Schema::dropIfExists('hongvan_page_locks');
        Schema::dropIfExists('hongvan_page_publish_schedules');
        Schema::dropIfExists('hongvan_page_versions');
        Schema::dropIfExists('hongvan_page_translations');
        Schema::dropIfExists('hongvan_pages');
    }
};
