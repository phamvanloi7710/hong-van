<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_notifications', function (Blueprint $table): void {
            $table->comment('Thông báo gửi tới người dùng hoặc thực thể có thể nhận thông báo');
            $table->uuid('id')->primary()->comment('UUID duy nhất của thông báo');
            $table->string('type')->comment('Tên class hoặc loại thông báo để Laravel khôi phục nội dung');
            $table->string('notifiable_type')->comment('Loại model nhận thông báo trong quan hệ đa hình');
            $table->unsignedBigInteger('notifiable_id')->comment('Khóa chính của model nhận thông báo');
            $table->text('data')->comment('Payload JSON chứa nội dung và metadata của thông báo');
            $table->timestamp('read_at')->nullable()->comment('Thời điểm UTC người nhận đọc thông báo; null nếu chưa đọc');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo thông báo');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật thông báo gần nhất');

            $table->index(['notifiable_type', 'notifiable_id']);
        });

        Schema::create('hongvan_personal_access_tokens', function (Blueprint $table): void {
            $table->comment('Personal access token do Laravel Sanctum quản lý');
            $table->id()->comment('Khóa chính tự tăng của personal access token');
            $table->string('tokenable_type')->comment('Loại model sở hữu token trong quan hệ đa hình');
            $table->unsignedBigInteger('tokenable_id')->comment('Khóa chính của model sở hữu token');
            $table->text('name')->comment('Tên gợi nhớ do người tạo đặt cho token');
            $table->string('token', 64)->unique()->comment('Giá trị token đã băm dùng để xác thực');
            $table->text('abilities')->nullable()->comment('Danh sách quyền của token dưới dạng JSON; null dùng quyền mặc định');
            $table->timestamp('last_used_at')->nullable()->comment('Thời điểm UTC token được sử dụng gần nhất');
            $table->timestamp('expires_at')->nullable()->index()->comment('Thời điểm UTC token hết hạn; null nghĩa là không đặt hạn');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo token');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật token gần nhất');

            $table->index(['tokenable_type', 'tokenable_id']);
        });

        Schema::create('hongvan_languages', function (Blueprint $table): void {
            $table->comment('Danh mục ngôn ngữ và locale được nền tảng hỗ trợ');
            $table->id()->comment('Khóa chính nội bộ của ngôn ngữ');
            $table->char('public_id', 26)->unique()->comment('ULID công khai của ngôn ngữ dùng trong API');
            $table->string('locale', 12)->unique()->comment('Mã locale chuẩn, ví dụ vi hoặc en');
            $table->string('name')->comment('Tên ngôn ngữ dùng trong khu vực quản trị');
            $table->string('native_name')->comment('Tên ngôn ngữ được viết bằng chính ngôn ngữ đó');
            $table->boolean('is_active')->default(true)->index()->comment('Cho biết ngôn ngữ có đang được phép sử dụng hay không');
            $table->boolean('is_default')->default(false)->index()->comment('Cho biết đây có phải ngôn ngữ mặc định của website hay không');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Thứ tự hiển thị tăng dần của ngôn ngữ');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo ngôn ngữ');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật ngôn ngữ gần nhất');
        });

        Schema::create('hongvan_setting_groups', function (Blueprint $table): void {
            $table->comment('Nhóm cấu hình dùng để tổ chức các thiết lập của nền tảng');
            $table->id()->comment('Khóa chính nội bộ của nhóm cấu hình');
            $table->char('public_id', 26)->unique()->comment('ULID công khai của nhóm cấu hình dùng trong API');
            $table->string('key')->unique()->comment('Khóa kỹ thuật duy nhất dùng để truy xuất nhóm cấu hình');
            $table->string('label')->comment('Tên hiển thị của nhóm cấu hình');
            $table->text('description')->nullable()->comment('Mô tả mục đích và phạm vi của nhóm cấu hình');
            $table->boolean('is_active')->default(true)->index()->comment('Cho biết nhóm cấu hình có đang được sử dụng hay không');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Thứ tự hiển thị tăng dần của nhóm cấu hình');
            $table->foreignId('created_by')->nullable()->comment('Tài khoản đã tạo nhóm cấu hình; null nếu được hệ thống tạo')->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Tài khoản cập nhật nhóm cấu hình gần nhất; null nếu do hệ thống cập nhật')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo nhóm cấu hình');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật nhóm cấu hình gần nhất');
        });

        Schema::create('hongvan_settings', function (Blueprint $table): void {
            $table->comment('Giá trị cấu hình thuộc từng nhóm thiết lập của nền tảng');
            $table->id()->comment('Khóa chính nội bộ của thiết lập');
            $table->char('public_id', 26)->unique()->comment('ULID công khai của thiết lập dùng trong API');
            $table->foreignId('setting_group_id')->comment('Nhóm chứa thiết lập này')->constrained('hongvan_setting_groups')->cascadeOnDelete();
            $table->string('key')->comment('Khóa kỹ thuật của thiết lập, duy nhất trong từng nhóm');
            $table->string('label')->comment('Tên hiển thị của thiết lập trong khu vực quản trị');
            $table->text('description')->nullable()->comment('Mô tả ý nghĩa và cách sử dụng thiết lập');
            $table->longText('value')->nullable()->comment('Giá trị thiết lập đã được chuẩn hóa theo value_type');
            $table->string('value_type', 32)->default('string')->comment('Kiểu dữ liệu logic dùng để validate và chuyển đổi value');
            $table->boolean('is_public')->default(false)->index()->comment('Cho biết thiết lập có được phép công khai ra website hoặc API public hay không');
            $table->boolean('is_locked')->default(false)->index()->comment('Cho biết thiết lập có bị khóa không cho chỉnh sửa thông thường hay không');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Thứ tự hiển thị tăng dần của thiết lập trong nhóm');
            $table->foreignId('created_by')->nullable()->comment('Tài khoản đã tạo thiết lập; null nếu được hệ thống tạo')->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Tài khoản cập nhật thiết lập gần nhất; null nếu do hệ thống cập nhật')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo thiết lập');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật thiết lập gần nhất');

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
