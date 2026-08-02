<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_audit_logs', function (Blueprint $table): void {
            $table->comment('Nhật ký hoạt động bất biến dùng để truy vết thao tác quản trị và sự kiện bảo mật quan trọng');
            $table->id()->comment('Khóa chính nội bộ tự tăng của bản ghi audit');
            $table->char('public_id', 26)->unique()->comment('ULID công khai bất biến của bản ghi audit dùng trong API quản trị');
            $table->string('actor_type', 32)->default('system')->index()->comment('Loại chủ thể thực hiện hành động: user, anonymous hoặc system');
            $table->char('actor_public_id', 26)->nullable()->index()->comment('ULID của tài khoản thực hiện tại thời điểm ghi log; null với khách hoặc tiến trình hệ thống');
            $table->string('action', 191)->index()->comment('Mã hành động ổn định theo namespace nghiệp vụ, ví dụ identity.user.updated');
            $table->string('subject_type', 100)->nullable()->comment('Loại đối tượng chịu tác động, dùng identifier nghiệp vụ ổn định thay vì tên bảng từ request');
            $table->char('subject_public_id', 26)->nullable()->comment('Định danh công khai của đối tượng chịu tác động; null nếu sự kiện không gắn với một đối tượng');
            $table->json('before_data')->nullable()->comment('Ảnh chụp hoặc phần chênh lệch trước thay đổi đã loại bỏ dữ liệu nhạy cảm');
            $table->json('after_data')->nullable()->comment('Ảnh chụp hoặc phần chênh lệch sau thay đổi đã loại bỏ dữ liệu nhạy cảm');
            $table->json('metadata')->nullable()->comment('Metadata kỹ thuật đã redaction, không chứa password, token, cookie, secret hoặc nội dung file');
            $table->char('ip_hash', 64)->nullable()->index()->comment('HMAC SHA-256 của địa chỉ IP để đối chiếu sự kiện mà không lưu IP thô');
            $table->char('user_agent_hash', 64)->nullable()->comment('HMAC SHA-256 của User-Agent để đối chiếu thiết bị mà không lưu chuỗi thô');
            $table->char('request_id', 26)->index()->comment('ULID request dùng liên kết audit với response và security log');
            $table->timestamp('occurred_at')->useCurrent()->index()->comment('Thời điểm UTC sự kiện xảy ra; bản ghi không có updated_at vì chỉ được phép append');

            $table->index(['subject_type', 'subject_public_id']);
            $table->index(['action', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_audit_logs');
    }
};
