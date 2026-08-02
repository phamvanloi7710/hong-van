<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hongvan_languages', function (Blueprint $table): void {
            $table->foreignId('fallback_language_id')->nullable()->after('is_default')->comment('Ngôn ngữ được dùng khi bản dịch của locale hiện tại bị thiếu; null dùng ngôn ngữ mặc định')->constrained('hongvan_languages')->nullOnDelete();
        });

        Schema::create('hongvan_translation_keys', function (Blueprint $table): void {
            $table->comment('Danh mục khóa bản dịch dùng chung, được phân tách rõ theo namespace nghiệp vụ');
            $table->id()->comment('Khóa chính nội bộ của khóa bản dịch');
            $table->char('public_id', 26)->unique()->comment('ULID công khai của khóa bản dịch dùng trong API quản trị');
            $table->string('namespace', 100)->comment('Namespace nghiệp vụ của khóa, ví dụ common, validation hoặc public');
            $table->string('key', 191)->comment('Tên khóa duy nhất trong namespace, không chứa nội dung bản dịch');
            $table->text('description')->nullable()->comment('Mô tả ngữ cảnh và cách sử dụng khóa bản dịch cho biên tập viên');
            $table->boolean('is_system')->default(false)->index()->comment('Cho biết khóa do hệ thống quản lý và không được xóa tùy ý');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo khóa bản dịch');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật khóa bản dịch gần nhất');

            $table->unique(['namespace', 'key']);
        });

        Schema::create('hongvan_translation_values', function (Blueprint $table): void {
            $table->comment('Giá trị bản dịch theo từng khóa và ngôn ngữ, tách khỏi JSON để có thể tìm kiếm và báo cáo');
            $table->id()->comment('Khóa chính nội bộ của giá trị bản dịch');
            $table->foreignId('translation_key_id')->comment('Khóa bản dịch mà giá trị này thuộc về')->constrained('hongvan_translation_keys')->cascadeOnDelete();
            $table->foreignId('language_id')->comment('Ngôn ngữ của giá trị bản dịch')->constrained('hongvan_languages')->cascadeOnDelete();
            $table->longText('value')->nullable()->comment('Nội dung bản dịch; null hoặc chuỗi rỗng được tính là còn thiếu');
            $table->boolean('is_reviewed')->default(false)->index()->comment('Cho biết nội dung đã được người có trách nhiệm rà soát hay chưa');
            $table->foreignId('translated_by')->nullable()->comment('Tài khoản cập nhật bản dịch gần nhất; null nếu do seeder hệ thống tạo')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC tạo giá trị bản dịch');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật giá trị bản dịch gần nhất');

            $table->unique(['translation_key_id', 'language_id']);
        });

        Schema::create('hongvan_localized_slugs', function (Blueprint $table): void {
            $table->comment('Sổ đăng ký slug theo locale và namespace để ngăn xung đột giữa các nội dung public');
            $table->id()->comment('Khóa chính nội bộ của bản ghi giữ chỗ slug');
            $table->foreignId('language_id')->comment('Ngôn ngữ sở hữu slug')->constrained('hongvan_languages')->cascadeOnDelete();
            $table->string('namespace', 100)->comment('Không gian route của slug, ví dụ pages, products hoặc posts');
            $table->string('slug', 191)->comment('Slug đã chuẩn hóa dùng trong URL public');
            $table->string('owner_type', 100)->comment('Loại entity sở hữu slug, dùng tên contract ổn định thay vì tên bảng từ request');
            $table->string('owner_key', 191)->comment('Định danh ổn định của entity sở hữu slug, thường là public_id');
            $table->timestamp('created_at')->nullable()->comment('Thời điểm UTC đăng ký slug');
            $table->timestamp('updated_at')->nullable()->comment('Thời điểm UTC cập nhật đăng ký slug gần nhất');

            $table->unique(['language_id', 'namespace', 'slug']);
            $table->unique(['owner_type', 'owner_key', 'language_id', 'namespace'], 'hongvan_localized_slugs_owner_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_localized_slugs');
        Schema::dropIfExists('hongvan_translation_values');
        Schema::dropIfExists('hongvan_translation_keys');

        Schema::table('hongvan_languages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('fallback_language_id');
        });
    }
};
