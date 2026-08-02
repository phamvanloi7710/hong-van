<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hongvan_media_folders', function (Blueprint $table): void {
            $table->boolean('is_locked')->default(false)->index()->after('sort_order')
                ->comment('Prevents administrators from changing this folder until it is explicitly unlocked');
        });

        Schema::table('hongvan_media', function (Blueprint $table): void {
            $table->boolean('is_locked')->default(false)->index()->after('visibility')
                ->comment('Prevents metadata, folder, visibility, trash, or replacement changes until explicitly unlocked');
        });
    }

    public function down(): void
    {
        Schema::table('hongvan_media', function (Blueprint $table): void {
            $table->dropColumn('is_locked');
        });

        Schema::table('hongvan_media_folders', function (Blueprint $table): void {
            $table->dropColumn('is_locked');
        });
    }
};
