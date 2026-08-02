<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_jobs', function (Blueprint $table): void {
            $table->comment('Công việc nền đang chờ queue worker xử lý');
            $table->id()->comment('Khóa chính tự tăng của công việc nền');
            $table->string('queue')->index()->comment('Tên hàng đợi chứa công việc');
            $table->longText('payload')->comment('Nội dung công việc đã được Laravel tuần tự hóa');
            $table->unsignedTinyInteger('attempts')->comment('Số lần worker đã thử thực thi công việc');
            $table->unsignedInteger('reserved_at')->nullable()->comment('Unix timestamp khi công việc được worker giữ để xử lý');
            $table->unsignedInteger('available_at')->comment('Unix timestamp sớm nhất công việc được phép chạy');
            $table->unsignedInteger('created_at')->comment('Unix timestamp tạo công việc');
        });

        Schema::create('hongvan_job_batches', function (Blueprint $table): void {
            $table->comment('Thông tin theo dõi một nhóm công việc queue chạy theo batch');
            $table->string('id')->primary()->comment('Mã định danh duy nhất của batch');
            $table->string('name')->comment('Tên mô tả batch công việc');
            $table->integer('total_jobs')->comment('Tổng số công việc ban đầu trong batch');
            $table->integer('pending_jobs')->comment('Số công việc còn chờ xử lý trong batch');
            $table->integer('failed_jobs')->comment('Số công việc đã thất bại trong batch');
            $table->longText('failed_job_ids')->comment('Danh sách mã công việc thất bại đã được tuần tự hóa');
            $table->mediumText('options')->nullable()->comment('Tùy chọn và callback của batch đã được tuần tự hóa');
            $table->integer('cancelled_at')->nullable()->comment('Unix timestamp khi batch bị hủy; null nếu chưa hủy');
            $table->integer('created_at')->comment('Unix timestamp tạo batch');
            $table->integer('finished_at')->nullable()->comment('Unix timestamp hoàn tất batch; null nếu chưa hoàn tất');
        });

        Schema::create('hongvan_failed_jobs', function (Blueprint $table): void {
            $table->comment('Lịch sử các công việc queue thực thi thất bại');
            $table->id()->comment('Khóa chính tự tăng của bản ghi thất bại');
            $table->string('uuid')->unique()->comment('UUID duy nhất của công việc bị thất bại');
            $table->text('connection')->comment('Tên kết nối queue đã thực thi công việc');
            $table->text('queue')->comment('Tên hàng đợi chứa công việc thất bại');
            $table->longText('payload')->comment('Nội dung công việc thất bại đã được tuần tự hóa');
            $table->longText('exception')->comment('Thông tin exception và stack trace khi công việc thất bại');
            $table->timestamp('failed_at')->useCurrent()->comment('Thời điểm UTC ghi nhận công việc thất bại');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_failed_jobs');
        Schema::dropIfExists('hongvan_job_batches');
        Schema::dropIfExists('hongvan_jobs');
    }
};
