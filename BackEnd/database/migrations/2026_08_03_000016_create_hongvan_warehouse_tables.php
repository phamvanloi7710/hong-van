<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_warehouse_facilities', function (Blueprint $table): void {
            $table->comment('Reusable public-facing warehouse facility catalog such as security or loading capability');
            $table->id()->comment('Internal primary key of the warehouse facility');
            $table->char('public_id', 26)->unique()->comment('Public ULID used by administration APIs');
            $table->string('code', 64)->unique()->comment('Stable administrator-defined facility code');
            $table->string('icon', 64)->nullable()->comment('Optional allowlisted Material icon name for presentation');
            $table->boolean('is_active')->default(true)->index()->comment('Whether the facility is available for warehouse assignment');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the facility');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the facility')->constrained('hongvan_users', 'id', 'hv_wf_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the facility')->constrained('hongvan_users', 'id', 'hv_wf_updated_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the facility was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the facility was most recently updated');
        });
        Schema::create('hongvan_warehouse_facility_translations', function (Blueprint $table): void {
            $table->comment('Localized warehouse facility names and descriptions');
            $table->id()->comment('Internal primary key of the facility translation');
            $table->foreignId('warehouse_facility_id')->comment('Facility that owns the localized content')->constrained('hongvan_warehouse_facilities', 'id', 'hv_wft_facility_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Localized public facility name');
            $table->text('description')->nullable()->comment('Optional localized facility description');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['warehouse_facility_id', 'locale'], 'hv_wft_locale_uniq');
        });
        Schema::create('hongvan_warehouse_services', function (Blueprint $table): void {
            $table->comment('Reusable public-facing warehouse service catalog without operational fulfillment workflows');
            $table->id()->comment('Internal primary key of the warehouse service');
            $table->char('public_id', 26)->unique()->comment('Public ULID used by administration APIs');
            $table->string('code', 64)->unique()->comment('Stable administrator-defined warehouse service code');
            $table->string('icon', 64)->nullable()->comment('Optional allowlisted Material icon name for presentation');
            $table->boolean('is_active')->default(true)->index()->comment('Whether the service is available for warehouse assignment');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the service');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the service')->constrained('hongvan_users', 'id', 'hv_ws_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the service')->constrained('hongvan_users', 'id', 'hv_ws_updated_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the service was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the service was most recently updated');
        });
        Schema::create('hongvan_warehouse_service_translations', function (Blueprint $table): void {
            $table->comment('Localized warehouse service names and descriptions');
            $table->id()->comment('Internal primary key of the service translation');
            $table->foreignId('warehouse_service_id')->comment('Service that owns the localized content')->constrained('hongvan_warehouse_services', 'id', 'hv_wst_service_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Localized public service name');
            $table->text('description')->nullable()->comment('Optional localized service description');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['warehouse_service_id', 'locale'], 'hv_wst_locale_uniq');
        });
        Schema::create('hongvan_warehouses', function (Blueprint $table): void {
            $table->comment('Public warehouse capability profiles without stock, bin, inventory or inbound-outbound operations');
            $table->id()->comment('Internal primary key of the warehouse profile');
            $table->char('public_id', 26)->unique()->comment('Public ULID used by administration APIs');
            $table->string('code', 100)->unique()->comment('Stable administrator-defined warehouse profile code');
            $table->decimal('area_value', 14, 3)->nullable()->comment('Optional representative available or total area value');
            $table->string('area_unit', 16)->nullable()->comment('Unit for the representative area, such as m2');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Optional map latitude; public precision follows map display mode');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Optional map longitude; public precision follows map display mode');
            $table->string('map_display', 24)->default('hidden')->index()->comment('Public map privacy mode: hidden, approximate, or exact');
            $table->json('business_hours')->nullable()->comment('Structured weekly business-hours display data, not an operating schedule engine');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial status: draft, published, scheduled, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the warehouse is eligible for featured placement');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending public warehouse display order');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the warehouse becomes publicly visible');
            $table->timestamp('unpublished_at')->nullable()->index()->comment('Optional UTC time when visibility ends');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the warehouse')->constrained('hongvan_users', 'id', 'hv_wh_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the warehouse')->constrained('hongvan_users', 'id', 'hv_wh_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the warehouse to trash')->constrained('hongvan_users', 'id', 'hv_wh_deleted_by_fk')->nullOnDelete();
            $table->softDeletes()->comment('UTC time when the warehouse was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the warehouse was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the warehouse was most recently updated');
        });
        Schema::create('hongvan_warehouse_translations', function (Blueprint $table): void {
            $table->comment('Localized warehouse profile, address, capacity, security, fire-safety and hours descriptions');
            $table->id()->comment('Internal primary key of the warehouse translation');
            $table->foreignId('warehouse_id')->comment('Warehouse that owns the localized content')->constrained('hongvan_warehouses', 'id', 'hv_wht_warehouse_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Localized public warehouse name');
            $table->string('slug', 191)->comment('Localized public slug unique inside the warehouse namespace');
            $table->text('summary')->nullable()->comment('Optional localized warehouse card summary');
            $table->longText('description')->nullable()->comment('Optional sanitized localized warehouse description');
            $table->text('address_display')->nullable()->comment('Optional localized public address or location description');
            $table->text('area_description')->nullable()->comment('Localized context for area figures and available space');
            $table->text('capacity_description')->nullable()->comment('Localized descriptive capacity without inventory commitments');
            $table->text('security_description')->nullable()->comment('Localized description of security capabilities');
            $table->text('fire_safety_description')->nullable()->comment('Localized description of fire-safety capabilities without unverified claims');
            $table->text('business_hours_description')->nullable()->comment('Localized human-readable business-hours note');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title preparation field');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description preparation field');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['warehouse_id', 'locale'], 'hv_wht_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_wht_slug_uniq');
        });
        Schema::create('hongvan_warehouse_media', function (Blueprint $table): void {
            $table->comment('Ordered media library gallery assigned to public warehouse profiles');
            $table->foreignId('warehouse_id')->comment('Warehouse owning the media assignment')->constrained('hongvan_warehouses', 'id', 'hv_wm_warehouse_fk')->cascadeOnDelete();
            $table->foreignId('media_id')->comment('Media library asset assigned to the warehouse')->constrained('hongvan_media', 'id', 'hv_wm_media_fk')->cascadeOnDelete();
            $table->string('role', 24)->default('gallery')->comment('Presentation role: hero, gallery, or floorplan');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending gallery order');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the media assignment was created');
            $table->primary(['warehouse_id', 'media_id']);
            $table->index(['warehouse_id', 'role', 'sort_order'], 'hv_wm_order_idx');
        });
        Schema::create('hongvan_warehouse_facility_assignments', function (Blueprint $table): void {
            $table->comment('Many-to-many assignment of descriptive facilities to warehouse profiles');
            $table->foreignId('warehouse_id')->comment('Warehouse receiving the facility assignment')->constrained('hongvan_warehouses', 'id', 'hv_wfa_warehouse_fk')->cascadeOnDelete();
            $table->foreignId('warehouse_facility_id')->comment('Facility assigned to the warehouse')->constrained('hongvan_warehouse_facilities', 'id', 'hv_wfa_facility_fk')->restrictOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending facility order inside the warehouse profile');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the assignment was created');
            $table->primary(['warehouse_id', 'warehouse_facility_id'], 'hv_wfa_primary');
        });
        Schema::create('hongvan_warehouse_service_assignments', function (Blueprint $table): void {
            $table->comment('Many-to-many assignment of descriptive services to warehouse profiles');
            $table->foreignId('warehouse_id')->comment('Warehouse receiving the service assignment')->constrained('hongvan_warehouses', 'id', 'hv_wsa_warehouse_fk')->cascadeOnDelete();
            $table->foreignId('warehouse_service_id')->comment('Service assigned to the warehouse')->constrained('hongvan_warehouse_services', 'id', 'hv_wsa_service_fk')->restrictOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending service order inside the warehouse profile');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the assignment was created');
            $table->primary(['warehouse_id', 'warehouse_service_id'], 'hv_wsa_primary');
        });
        Schema::create('hongvan_warehouse_requests', function (Blueprint $table): void {
            $table->comment('Public warehouse rental enquiry contract without reservation, stock or operational workflow');
            $table->id()->comment('Internal primary key of the warehouse request');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the request safely');
            $table->string('status', 24)->default('new')->index()->comment('Request workflow status: new, reviewing, contacted, closed, or cancelled');
            $table->text('goods_description')->comment('User-provided description of goods requiring storage');
            $table->decimal('required_area', 14, 3)->nullable()->comment('Optional estimated storage area requested by the user');
            $table->string('area_unit', 16)->nullable()->comment('Unit of the requested area, such as m2');
            $table->decimal('required_volume', 14, 3)->nullable()->comment('Optional estimated storage volume requested by the user');
            $table->string('volume_unit', 16)->nullable()->comment('Unit of the requested volume, such as m3');
            $table->string('duration_description')->nullable()->comment('User-provided expected rental duration description');
            $table->date('start_date')->nullable()->index()->comment('Optional preferred rental start date, not a confirmed reservation');
            $table->text('storage_requirements')->nullable()->comment('Optional storage, handling or environmental requirements');
            $table->string('preferred_location')->nullable()->comment('Optional preferred warehouse location description');
            $table->foreignId('warehouse_id')->nullable()->comment('Optional preferred warehouse profile')->constrained('hongvan_warehouses', 'id', 'hv_wreq_warehouse_fk')->nullOnDelete();
            $table->string('contact_name')->comment('Requester contact name');
            $table->string('contact_phone', 32)->comment('Requester contact phone');
            $table->string('contact_email')->nullable()->comment('Optional requester contact email');
            $table->string('ip_hash', 64)->nullable()->index()->comment('Privacy-preserving hash of the submission IP for abuse review');
            $table->string('user_agent_hash', 64)->nullable()->comment('Privacy-preserving hash of the user agent for abuse review');
            $table->timestamp('created_at')->nullable()->index()->comment('UTC time when the request was submitted');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the request was most recently updated');
        });
        Schema::create('hongvan_warehouse_request_status_histories', function (Blueprint $table): void {
            $table->comment('Append-only status history for warehouse rental enquiries');
            $table->id()->comment('Internal primary key of the status history entry');
            $table->foreignId('warehouse_request_id')->comment('Warehouse request whose status changed')->constrained('hongvan_warehouse_requests', 'id', 'hv_wrsh_request_fk')->cascadeOnDelete();
            $table->string('from_status', 24)->nullable()->comment('Previous request status, or null for initial submission');
            $table->string('to_status', 24)->comment('New request status');
            $table->text('note')->nullable()->comment('Optional internal status change note');
            $table->foreignId('changed_by')->nullable()->comment('Administrator who changed the status, or null for public submission')->constrained('hongvan_users', 'id', 'hv_wrsh_changed_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->index()->comment('UTC time when the status change occurred');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_warehouse_request_status_histories');
        Schema::dropIfExists('hongvan_warehouse_requests');
        Schema::dropIfExists('hongvan_warehouse_service_assignments');
        Schema::dropIfExists('hongvan_warehouse_facility_assignments');
        Schema::dropIfExists('hongvan_warehouse_media');
        Schema::dropIfExists('hongvan_warehouse_translations');
        Schema::dropIfExists('hongvan_warehouses');
        Schema::dropIfExists('hongvan_warehouse_service_translations');
        Schema::dropIfExists('hongvan_warehouse_services');
        Schema::dropIfExists('hongvan_warehouse_facility_translations');
        Schema::dropIfExists('hongvan_warehouse_facilities');
    }
};
