<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_cache', function (Blueprint $table): void {
            $table->comment('Dữ liệu cache ứng dụng được lưu bằng database');
            $table->string('key')->primary()->comment('Khóa duy nhất định danh mục cache');
            $table->mediumText('value')->comment('Giá trị cache đã được Laravel tuần tự hóa');
            $table->integer('expiration')->index()->comment('Unix timestamp hết hạn của mục cache');
        });

        Schema::create('hongvan_cache_locks', function (Blueprint $table): void {
            $table->comment('Khóa phân tán dùng để ngăn nhiều tiến trình xử lý cùng tài nguyên');
            $table->string('key')->primary()->comment('Khóa duy nhất định danh tài nguyên đang bị khóa');
            $table->string('owner')->comment('Token nhận diện tiến trình đang sở hữu khóa');
            $table->integer('expiration')->index()->comment('Unix timestamp hết hạn của khóa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_cache_locks');
        Schema::dropIfExists('hongvan_cache');
    }
};
