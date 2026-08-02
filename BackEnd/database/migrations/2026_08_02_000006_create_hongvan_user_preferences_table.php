<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_user_preferences', function (Blueprint $table): void {
            $table->comment('Cấu hình giao diện và trải nghiệm riêng của từng tài khoản quản trị');
            $table->id()->comment('Khóa chính tự tăng của cấu hình người dùng');
            $table->foreignId('user_id')->comment('Tài khoản sở hữu cấu hình')->constrained('hongvan_users')->cascadeOnDelete();
            $table->string('namespace', 64)->comment('Nhóm chức năng của cấu hình, ví dụ admin');
            $table->string('key', 64)->comment('Khóa cấu hình duy nhất trong namespace của người dùng');
            $table->json('value')->comment('Giá trị JSON đã được validate theo allowlist của khóa cấu hình');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo cấu hình');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật cấu hình gần nhất');

            $table->unique(['user_id', 'namespace', 'key']);
            $table->index(['namespace', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_user_preferences');
    }
};
