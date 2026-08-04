<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hongvan_page_versions', function (Blueprint $table): void {
            $table->string('note', 500)->nullable()->after('checksum')->comment('Ghi chú do quản trị viên nhập khi tạo mốc phiên bản bất biến.');
        });
    }

    public function down(): void
    {
        Schema::table('hongvan_page_versions', function (Blueprint $table): void {
            $table->dropColumn('note');
        });
    }
};
