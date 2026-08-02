<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_branches', function (Blueprint $table): void {
            $table->comment('Company branches, offices, warehouses, and other public business locations');
            $table->id()->comment('Internal primary key of the branch');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the branch in APIs');
            $table->string('name')->comment('Display name of the branch or business location');
            $table->string('code', 64)->nullable()->unique()->comment('Optional internal unique code of the branch');
            $table->text('address')->nullable()->comment('Full postal address supplied by an administrator');
            $table->string('province', 120)->nullable()->comment('Province or municipality of the branch');
            $table->string('district', 120)->nullable()->comment('District of the branch');
            $table->string('ward', 120)->nullable()->comment('Ward or commune of the branch');
            $table->string('postal_code', 32)->nullable()->comment('Postal code of the branch address');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Geographic latitude used to display the branch on a map');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Geographic longitude used to display the branch on a map');
            $table->string('phone', 32)->nullable()->comment('Public telephone number of the branch');
            $table->string('email')->nullable()->comment('Public contact email address of the branch');
            $table->boolean('is_head_office')->default(false)->index()->comment('Indicates whether this branch is the company head office');
            $table->boolean('is_active')->default(true)->index()->comment('Indicates whether this branch may be displayed and used');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the branch');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the branch; null for a system operation')->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently updated the branch; null for a system operation')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the branch was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the branch was most recently updated');
        });

        Schema::create('hongvan_business_hours', function (Blueprint $table): void {
            $table->comment('Weekly business hours for the company or an individual branch');
            $table->id()->comment('Internal primary key of the business-hours row');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the business-hours row in APIs');
            $table->foreignId('branch_id')->nullable()->comment('Branch these hours belong to; null means company-wide hours')->constrained('hongvan_branches')->cascadeOnDelete();
            $table->string('scope_key', 64)->comment('Stable scope identifier used to enforce one row per weekday');
            $table->unsignedTinyInteger('day_of_week')->comment('Weekday number from 0 Sunday through 6 Saturday');
            $table->time('opens_at')->nullable()->comment('Local opening time; null when the day is closed or unspecified');
            $table->time('closes_at')->nullable()->comment('Local closing time; null when the day is closed or unspecified');
            $table->boolean('is_closed')->default(false)->comment('Indicates that the business is closed for this entire weekday');
            $table->string('note')->nullable()->comment('Optional public note about the hours for this weekday');
            $table->boolean('is_active')->default(true)->index()->comment('Indicates whether this business-hours row is currently used');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the business-hours row');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the row; null for a system operation')->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently updated the row; null for a system operation')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the business-hours row was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the business-hours row was most recently updated');

            $table->unique(['scope_key', 'day_of_week']);
            $table->index(['branch_id', 'is_active', 'sort_order']);
        });

        Schema::create('hongvan_social_links', function (Blueprint $table): void {
            $table->comment('Ordered links to the company social-network profiles');
            $table->id()->comment('Internal primary key of the social link');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the social link in APIs');
            $table->string('platform', 64)->comment('Technical platform identifier such as facebook or youtube');
            $table->string('label')->comment('Administrator-supplied display label of the social link');
            $table->text('url')->comment('Absolute HTTPS URL of the company social profile');
            $table->string('icon', 64)->nullable()->comment('Optional approved icon identifier used by the user interface');
            $table->boolean('is_active')->default(true)->index()->comment('Indicates whether the social link may be displayed publicly');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the social link');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the social link; null for a system operation')->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently updated the social link; null for a system operation')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the social link was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the social link was most recently updated');

            $table->index(['platform', 'is_active']);
        });

        Schema::create('hongvan_contact_channels', function (Blueprint $table): void {
            $table->comment('Ordered public channels through which customers may contact the company');
            $table->id()->comment('Internal primary key of the contact channel');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the contact channel in APIs');
            $table->string('type', 64)->comment('Technical channel type such as phone, email, zalo, or messenger');
            $table->string('label')->comment('Administrator-supplied display label of the contact channel');
            $table->string('value')->comment('Public telephone number, address, account, or other channel value');
            $table->text('href')->nullable()->comment('Optional sanitized link opened when a visitor selects the contact channel');
            $table->string('availability_note')->nullable()->comment('Optional public note describing when this channel is available');
            $table->boolean('is_primary')->default(false)->index()->comment('Indicates that this is a preferred company contact channel');
            $table->boolean('is_active')->default(true)->index()->comment('Indicates whether the contact channel may be displayed publicly');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the contact channel');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the contact channel; null for a system operation')->constrained('hongvan_users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently updated the contact channel; null for a system operation')->constrained('hongvan_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the contact channel was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the contact channel was most recently updated');

            $table->index(['type', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_contact_channels');
        Schema::dropIfExists('hongvan_social_links');
        Schema::dropIfExists('hongvan_business_hours');
        Schema::dropIfExists('hongvan_branches');
    }
};
