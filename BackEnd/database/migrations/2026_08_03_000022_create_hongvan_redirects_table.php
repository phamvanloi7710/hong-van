<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_redirects', function (Blueprint $table): void {
            $table->comment('Exact-path public redirect and gone-response rules managed by administrators');
            $table->id()->comment('Internal primary key used only by the application');
            $table->ulid('public_id')->unique()->comment('Stable public ULID exposed by the admin API');
            $table->string('source_path', 500)->comment('Normalized exact public request path beginning with a slash and excluding query strings');
            $table->string('locale', 10)->default('*')->comment('Locale scope for the rule, or an asterisk for every locale');
            $table->string('target_path', 500)->nullable()->comment('Normalized internal destination path; null only when status code is 410 Gone');
            $table->unsignedSmallInteger('status_code')->comment('HTTP response code allowlisted to 301, 302 or 410');
            $table->boolean('is_active')->default(true)->comment('Whether the rule may currently resolve public requests');
            $table->unsignedBigInteger('hit_count')->default(0)->comment('Number of public requests resolved by this rule');
            $table->timestamp('last_hit_at')->nullable()->comment('UTC time when this rule most recently resolved a public request');
            $table->string('note', 1000)->nullable()->comment('Private administrator note explaining the redirect purpose');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the rule')->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently updated the rule')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the rule was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the rule was most recently updated');

            $table->unique(['source_path', 'locale'], 'hv_p43_redirect_source_locale_uq');
            $table->index(['is_active', 'locale'], 'hv_p43_redirect_active_locale_idx');
        });

        $groupId = DB::table('hongvan_setting_groups')->where('key', 'seo_defaults')->value('id');
        if (is_numeric($groupId)) {
            DB::table('hongvan_settings')->insertOrIgnore([
                'public_id' => (string) Str::ulid(), 'setting_group_id' => (int) $groupId,
                'key' => 'robots_disallow_paths', 'label' => 'Robots disallow paths',
                'description' => 'One internal path per line that compliant crawlers should not crawl.',
                'value' => "/admin\n/api\n/preview", 'value_type' => 'text', 'is_public' => false,
                'is_locked' => false, 'sort_order' => 3, 'created_at' => now('UTC'), 'updated_at' => now('UTC'),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('hongvan_settings')->where('key', 'robots_disallow_paths')->delete();
        Schema::dropIfExists('hongvan_redirects');
    }
};
