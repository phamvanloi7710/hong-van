<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_themes', function (Blueprint $table): void {
            $table->comment('Public website theme identities with a pointer to the currently published immutable version');
            $table->id()->comment('Internal primary key of the public theme');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the theme safely');
            $table->string('key', 80)->unique()->comment('Stable machine key of the public theme');
            $table->string('name', 160)->comment('Administrator-facing theme name');
            $table->text('description')->nullable()->comment('Administrator-facing description of the theme purpose');
            $table->boolean('is_active')->default(false)->index()->comment('Whether this theme supplies the public website appearance');
            $table->unsignedBigInteger('published_version_id')->nullable()->index()->comment('Immutable version currently served to public visitors');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the theme')->constrained('hongvan_users', 'id', 'hv_themes_creator_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who last changed theme metadata')->constrained('hongvan_users', 'id', 'hv_themes_updater_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the theme was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the theme metadata last changed');
        });

        Schema::create('hongvan_theme_versions', function (Blueprint $table): void {
            $table->comment('Versioned allowlisted public theme token documents and their server-compiled CSS variables');
            $table->id()->comment('Internal primary key of the theme version');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the theme version safely');
            $table->foreignId('theme_id')->comment('Theme that owns this immutable or editable version')->constrained('hongvan_themes', 'id', 'hv_theme_versions_theme_fk')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->comment('Monotonically increasing version number within the theme');
            $table->string('status', 24)->index()->comment('Lifecycle state: draft, published, or discarded');
            $table->json('tokens')->comment('Validated design token document containing only server-allowlisted keys and values');
            $table->longText('compiled_css')->comment('Server-generated CSS custom properties compiled only from allowlisted tokens');
            $table->char('checksum', 64)->index()->comment('SHA-256 checksum of the canonical token document for cache identity');
            $table->foreignId('parent_version_id')->nullable()->comment('Earlier version from which this version was cloned')->constrained('hongvan_theme_versions', 'id', 'hv_theme_versions_parent_fk')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->comment('Administrator who created or started this version')->constrained('hongvan_users', 'id', 'hv_theme_versions_creator_fk')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->comment('Administrator who published this version')->constrained('hongvan_users', 'id', 'hv_theme_versions_publisher_fk')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when this version was published');
            $table->timestamp('created_at')->nullable()->comment('UTC time when this version was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when this draft was last updated');
            $table->unique(['theme_id', 'version_number'], 'hv_theme_versions_number_unique');
            $table->index(['theme_id', 'status', 'version_number'], 'hv_theme_versions_status_idx');
        });

        Schema::table('hongvan_themes', function (Blueprint $table): void {
            $table->foreign('published_version_id', 'hv_themes_published_version_fk')->references('id')->on('hongvan_theme_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hongvan_themes', function (Blueprint $table): void {
            $table->dropForeign('hv_themes_published_version_fk');
        });
        Schema::dropIfExists('hongvan_theme_versions');
        Schema::dropIfExists('hongvan_themes');
    }
};
