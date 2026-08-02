<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hongvan_users', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->index()->comment('Cho biết tài khoản có được phép đăng nhập và sử dụng hệ thống hay không')->after('email_verified_at');
            $table->timestamp('locked_at')->nullable()->index()->comment('Thời điểm UTC tài khoản bị khóa; null nghĩa là không bị khóa')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('hongvan_users', function (Blueprint $table): void {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['locked_at']);
            $table->dropColumn(['is_active', 'locked_at']);
        });
    }
};
