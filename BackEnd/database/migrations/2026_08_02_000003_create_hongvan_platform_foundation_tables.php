<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hongvan_personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('hongvan_languages', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('locale', 12)->unique();
            $table->string('name');
            $table->string('native_name');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('hongvan_setting_groups', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by')->nullable()->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('hongvan_users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('hongvan_settings', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('setting_group_id')->constrained('hongvan_setting_groups')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->text('description')->nullable();
            $table->longText('value')->nullable();
            $table->string('value_type', 32)->default('string');
            $table->boolean('is_public')->default(false)->index();
            $table->boolean('is_locked')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by')->nullable()->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('hongvan_users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['setting_group_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_settings');
        Schema::dropIfExists('hongvan_setting_groups');
        Schema::dropIfExists('hongvan_languages');
        Schema::dropIfExists('hongvan_personal_access_tokens');
        Schema::dropIfExists('hongvan_notifications');
    }
};
