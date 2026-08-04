<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_page_template_categories', function (Blueprint $table): void {
            $table->comment('Reusable categories used to organize Page Builder templates in the administrator library');
            $table->id()->comment('Internal primary key of the Page Builder template category');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the template category safely');
            $table->string('key', 80)->unique()->comment('Stable machine key used to identify the template category');
            $table->string('name', 120)->comment('Administrator-facing template category name');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of this category in the template library');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the template category was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the template category was last changed');
        });

        Schema::table('hongvan_page_templates', function (Blueprint $table): void {
            $table->foreignId('page_template_category_id')->nullable()->after('description')->index()->comment('Optional category that groups this reusable template in the administrator library')->constrained('hongvan_page_template_categories', 'id', 'hv_page_templates_category_fk')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hongvan_page_templates', function (Blueprint $table): void {
            $table->dropForeign('hv_page_templates_category_fk');
            $table->dropColumn('page_template_category_id');
        });
        Schema::dropIfExists('hongvan_page_template_categories');
    }
};
