<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_roles', function (Blueprint $table): void {
            $table->comment('Vai trò RBAC dùng để gom nhóm quyền cho người dùng');
            $table->id()->comment('Khóa chính nội bộ của vai trò');
            $table->char('public_id', 26)->unique()->comment('ULID công khai của vai trò dùng trong API');
            $table->string('name', 120)->comment('Tên hiển thị của vai trò');
            $table->string('slug', 100)->unique()->comment('Mã kỹ thuật duy nhất dùng để kiểm tra vai trò trong code');
            $table->text('description')->nullable()->comment('Mô tả phạm vi trách nhiệm của vai trò');
            $table->boolean('is_system')->default(false)->index()->comment('Cho biết vai trò hệ thống được bảo vệ khỏi thao tác xóa hoặc sửa nguy hiểm');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo vai trò');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật vai trò gần nhất');
        });

        Schema::create('hongvan_permissions', function (Blueprint $table): void {
            $table->comment('Danh mục quyền RBAC có thể cấp cho vai trò hoặc ghi đè theo người dùng');
            $table->id()->comment('Khóa chính nội bộ của quyền');
            $table->char('public_id', 26)->unique()->comment('ULID công khai của quyền dùng trong API');
            $table->string('key', 160)->unique()->comment('Mã quyền duy nhất theo định dạng module.action');
            $table->string('module', 100)->index()->comment('Mã module nghiệp vụ mà quyền thuộc về');
            $table->string('action', 40)->index()->comment('Hành động được quyền cho phép trong module');
            $table->string('name', 160)->comment('Tên quyền hiển thị trong khu vực quản trị');
            $table->text('description')->nullable()->comment('Mô tả chi tiết phạm vi mà quyền cho phép');
            $table->boolean('is_system')->default(false)->index()->comment('Cho biết quyền hệ thống được bảo vệ khỏi thao tác xóa hoặc sửa nguy hiểm');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo quyền');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật quyền gần nhất');

            $table->unique(['module', 'action']);
        });

        Schema::create('hongvan_role_user', function (Blueprint $table): void {
            $table->comment('Quan hệ gán vai trò RBAC cho từng tài khoản người dùng');
            $table->foreignId('role_id')->comment('Vai trò được gán cho người dùng')->constrained('hongvan_roles')->cascadeOnDelete();
            $table->foreignId('user_id')->comment('Tài khoản được gán vai trò')->constrained('hongvan_users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->comment('Tài khoản quản trị thực hiện việc gán; null nếu do hệ thống')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC gán vai trò cho người dùng');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật lần gán vai trò gần nhất');

            $table->primary(['role_id', 'user_id']);
            $table->index(['user_id', 'role_id']);
        });

        Schema::create('hongvan_permission_role', function (Blueprint $table): void {
            $table->comment('Quan hệ cấp quyền RBAC cho từng vai trò');
            $table->foreignId('permission_id')->comment('Quyền được cấp cho vai trò')->constrained('hongvan_permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->comment('Vai trò nhận quyền')->constrained('hongvan_roles')->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->comment('Tài khoản quản trị thực hiện việc cấp quyền; null nếu do hệ thống')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC cấp quyền cho vai trò');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật lần cấp quyền gần nhất');

            $table->primary(['permission_id', 'role_id']);
            $table->index(['role_id', 'permission_id']);
        });

        Schema::create('hongvan_user_permission_overrides', function (Blueprint $table): void {
            $table->comment('Quy tắc cho phép hoặc từ chối quyền riêng cho từng người dùng, ưu tiên hơn vai trò');
            $table->foreignId('user_id')->comment('Tài khoản nhận quy tắc ghi đè quyền')->constrained('hongvan_users')->cascadeOnDelete();
            $table->foreignId('permission_id')->comment('Quyền được ghi đè cho tài khoản')->constrained('hongvan_permissions')->cascadeOnDelete();
            $table->boolean('is_allowed')->comment('Giá trị true cho phép và false từ chối quyền đối với người dùng');
            $table->foreignId('assigned_by')->nullable()->comment('Tài khoản quản trị tạo quy tắc ghi đè; null nếu do hệ thống')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo quy tắc ghi đè quyền');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật quy tắc ghi đè quyền gần nhất');

            $table->primary(['user_id', 'permission_id']);
            $table->index(['permission_id', 'is_allowed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_user_permission_overrides');
        Schema::dropIfExists('hongvan_permission_role');
        Schema::dropIfExists('hongvan_role_user');
        Schema::dropIfExists('hongvan_permissions');
        Schema::dropIfExists('hongvan_roles');
    }
};
