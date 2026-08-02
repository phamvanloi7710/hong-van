<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `hongvan_migrations` COMMENT = 'Lịch sử các migration đã được Laravel thực thi cho cơ sở dữ liệu' ");
        DB::statement("ALTER TABLE `hongvan_migrations` MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính tự tăng của bản ghi migration'");
        DB::statement("ALTER TABLE `hongvan_migrations` MODIFY `migration` VARCHAR(255) NOT NULL COMMENT 'Tên file migration đã được thực thi'");
        DB::statement("ALTER TABLE `hongvan_migrations` MODIFY `batch` INT NOT NULL COMMENT 'Số thứ tự đợt chạy migration dùng cho rollback'");

        Schema::create('hongvan_users', function (Blueprint $table): void {
            $table->comment('Tài khoản người dùng có thể đăng nhập và sử dụng hệ thống');
            $table->id()->comment('Khóa chính nội bộ của tài khoản người dùng');
            $table->char('public_id', 26)->unique()->comment('ULID công khai dùng trong API và URL thay cho khóa chính nội bộ');
            $table->string('name')->comment('Tên hiển thị đầy đủ của người dùng');
            $table->string('email')->unique()->comment('Địa chỉ email duy nhất dùng để đăng nhập và liên hệ');
            $table->timestamp('email_verified_at')->nullable()->comment('Thời điểm email được xác minh; null nghĩa là chưa xác minh');
            $table->string('password')->comment('Mật khẩu đã băm của tài khoản; không lưu mật khẩu thuần');
            $table->string('remember_token', 100)->nullable()->comment('Token duy trì phiên đăng nhập khi người dùng chọn ghi nhớ đăng nhập');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo tài khoản');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật tài khoản gần nhất');
        });

        Schema::create('hongvan_password_reset_tokens', function (Blueprint $table): void {
            $table->comment('Token tạm thời phục vụ quy trình đặt lại mật khẩu');
            $table->string('email')->primary()->comment('Email của tài khoản yêu cầu đặt lại mật khẩu');
            $table->string('token')->comment('Token đặt lại mật khẩu đã được bảo vệ');
            $table->timestamp('created_at')->nullable()->index()->comment('Thời điểm UTC tạo token để kiểm tra thời hạn sử dụng');
        });

        Schema::create('hongvan_sessions', function (Blueprint $table): void {
            $table->comment('Phiên đăng nhập và phiên truy cập được lưu bằng database');
            $table->string('id')->primary()->comment('Mã định danh duy nhất của phiên');
            $table->foreignId('user_id')
                ->nullable()
                ->comment('Tài khoản sở hữu phiên; null đối với khách chưa đăng nhập')
                ->constrained('hongvan_users')
                ->nullOnDelete();
            $table->string('ip_address', 45)->nullable()->comment('Địa chỉ IPv4 hoặc IPv6 gần nhất của phiên');
            $table->text('user_agent')->nullable()->comment('Chuỗi nhận diện trình duyệt hoặc thiết bị của phiên');
            $table->longText('payload')->comment('Dữ liệu phiên đã được Laravel tuần tự hóa');
            $table->integer('last_activity')->index()->comment('Unix timestamp của lần hoạt động gần nhất');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_sessions');
        Schema::dropIfExists('hongvan_password_reset_tokens');
        Schema::dropIfExists('hongvan_users');
    }
};
