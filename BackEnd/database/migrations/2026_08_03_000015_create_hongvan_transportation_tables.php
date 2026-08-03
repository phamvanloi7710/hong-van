<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_vehicle_types', function (Blueprint $table): void {
            $table->comment('Public-facing vehicle type catalog used to group transportation capability entries');
            $table->id()->comment('Internal primary key of the vehicle type');
            $table->char('public_id', 26)->unique()->comment('Public ULID used by administration APIs');
            $table->string('code', 64)->unique()->comment('Stable administrator-defined vehicle type code');
            $table->boolean('is_active')->default(true)->index()->comment('Whether the vehicle type is available for fleet assignment');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order of the vehicle type');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the vehicle type')->constrained('hongvan_users', 'id', 'hv_vt_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the vehicle type')->constrained('hongvan_users', 'id', 'hv_vt_updated_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the record was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the record was most recently updated');
        });
        Schema::create('hongvan_vehicle_type_translations', function (Blueprint $table): void {
            $table->comment('Localized vehicle type names and descriptions');
            $table->id()->comment('Internal primary key of the vehicle type translation');
            $table->foreignId('vehicle_type_id')->comment('Vehicle type that owns the localized content')->constrained('hongvan_vehicle_types', 'id', 'hv_vtt_type_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Localized public vehicle type name');
            $table->text('description')->nullable()->comment('Optional localized vehicle type description');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['vehicle_type_id', 'locale'], 'hv_vtt_locale_uniq');
        });
        Schema::create('hongvan_vehicles', function (Blueprint $table): void {
            $table->comment('Public fleet capability entries without GPS, dispatch, maintenance or operational tracking');
            $table->id()->comment('Internal primary key of the vehicle capability entry');
            $table->char('public_id', 26)->unique()->comment('Public ULID used by administration APIs');
            $table->foreignId('vehicle_type_id')->comment('Vehicle type assigned to this capability entry')->constrained('hongvan_vehicle_types', 'id', 'hv_vehicles_type_fk')->restrictOnDelete();
            $table->string('code', 100)->unique()->comment('Stable public fleet entry code, not a live dispatch identifier');
            $table->decimal('payload_capacity', 12, 3)->nullable()->comment('Optional representative payload capacity value');
            $table->string('payload_unit', 16)->nullable()->comment('Unit for payload capacity such as kg or ton');
            $table->string('availability_display', 32)->default('contact')->index()->comment('Public availability display: available, limited, unavailable, or contact');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial status: draft, published, scheduled, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the vehicle entry is eligible for featured placement');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending public fleet display order');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the entry becomes publicly visible');
            $table->timestamp('unpublished_at')->nullable()->index()->comment('Optional UTC time when visibility ends');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the entry')->constrained('hongvan_users', 'id', 'hv_vehicles_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the entry')->constrained('hongvan_users', 'id', 'hv_vehicles_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the entry to trash')->constrained('hongvan_users', 'id', 'hv_vehicles_deleted_by_fk')->nullOnDelete();
            $table->softDeletes()->comment('UTC time when the vehicle entry was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the record was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the record was most recently updated');
            $table->index(['vehicle_type_id', 'status', 'sort_order'], 'hv_vehicles_catalog_idx');
        });
        Schema::create('hongvan_vehicle_translations', function (Blueprint $table): void {
            $table->comment('Localized fleet titles, body descriptions and cargo-box dimension descriptions');
            $table->id()->comment('Internal primary key of the vehicle translation');
            $table->foreignId('vehicle_id')->comment('Vehicle entry that owns this localized content')->constrained('hongvan_vehicles', 'id', 'hv_vtr_vehicle_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Localized public vehicle capability name');
            $table->string('slug', 191)->comment('Localized public slug unique inside the fleet namespace');
            $table->text('summary')->nullable()->comment('Optional localized fleet card summary');
            $table->longText('description')->nullable()->comment('Optional sanitized localized capability description');
            $table->text('body_dimensions')->nullable()->comment('Localized descriptive cargo-box or body dimensions without operational telemetry');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title preparation field');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description preparation field');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['vehicle_id', 'locale'], 'hv_vtr_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_vtr_slug_uniq');
        });
        Schema::create('hongvan_vehicle_media', function (Blueprint $table): void {
            $table->comment('Ordered media library gallery assigned to public fleet entries');
            $table->foreignId('vehicle_id')->comment('Vehicle entry owning the media assignment')->constrained('hongvan_vehicles', 'id', 'hv_vm_vehicle_fk')->cascadeOnDelete();
            $table->foreignId('media_id')->comment('Media library asset assigned to the vehicle entry')->constrained('hongvan_media', 'id', 'hv_vm_media_fk')->cascadeOnDelete();
            $table->string('role', 24)->default('gallery')->comment('Presentation role: hero or gallery');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending gallery order');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the media assignment was created');
            $table->primary(['vehicle_id', 'media_id']);
            $table->index(['vehicle_id', 'role', 'sort_order'], 'hv_vm_order_idx');
        });
        Schema::create('hongvan_transport_routes', function (Blueprint $table): void {
            $table->comment('Published representative transport route capabilities, not live trips or dispatch records');
            $table->id()->comment('Internal primary key of the transport route');
            $table->char('public_id', 26)->unique()->comment('Public ULID used by administration APIs');
            $table->string('code', 100)->unique()->comment('Stable administrator-defined route code');
            $table->string('origin_code', 100)->comment('Normalized origin label or internal code for filtering');
            $table->string('destination_code', 100)->comment('Normalized destination label or internal code for filtering');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial status: draft, published, scheduled, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the route is eligible for featured placement');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending route display order');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the route becomes publicly visible');
            $table->timestamp('unpublished_at')->nullable()->index()->comment('Optional UTC time when visibility ends');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the route')->constrained('hongvan_users', 'id', 'hv_tr_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the route')->constrained('hongvan_users', 'id', 'hv_tr_updated_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the record was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the record was most recently updated');
        });
        Schema::create('hongvan_transport_route_translations', function (Blueprint $table): void {
            $table->comment('Localized transport route names and descriptions');
            $table->id()->comment('Internal primary key of the route translation');
            $table->foreignId('transport_route_id')->comment('Transport route that owns this localized content')->constrained('hongvan_transport_routes', 'id', 'hv_trt_route_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Localized public route name');
            $table->string('slug', 191)->comment('Localized route slug unique inside the route namespace');
            $table->text('summary')->nullable()->comment('Optional localized route description');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['transport_route_id', 'locale'], 'hv_trt_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_trt_slug_uniq');
        });
        Schema::create('hongvan_transport_service_areas', function (Blueprint $table): void {
            $table->comment('Public transportation service-area capability entries without live geofencing');
            $table->id()->comment('Internal primary key of the service area');
            $table->char('public_id', 26)->unique()->comment('Public ULID used by administration APIs');
            $table->string('code', 100)->unique()->comment('Stable administrator-defined service area code');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial status: draft, published, scheduled, or archived');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the area is eligible for featured placement');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending service area display order');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the area becomes publicly visible');
            $table->timestamp('unpublished_at')->nullable()->index()->comment('Optional UTC time when visibility ends');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the area')->constrained('hongvan_users', 'id', 'hv_tsa_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the area')->constrained('hongvan_users', 'id', 'hv_tsa_updated_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the record was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the record was most recently updated');
        });
        Schema::create('hongvan_transport_service_area_translations', function (Blueprint $table): void {
            $table->comment('Localized transport service area names and descriptions');
            $table->id()->comment('Internal primary key of the service area translation');
            $table->foreignId('transport_service_area_id')->comment('Service area that owns this localized content')->constrained('hongvan_transport_service_areas', 'id', 'hv_tsat_area_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('Locale code of this translation');
            $table->string('name')->comment('Localized public service area name');
            $table->string('slug', 191)->comment('Localized slug unique inside the service area namespace');
            $table->text('summary')->nullable()->comment('Optional localized service area description');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['transport_service_area_id', 'locale'], 'hv_tsat_locale_uniq');
            $table->unique(['locale', 'slug'], 'hv_tsat_slug_uniq');
        });
        Schema::create('hongvan_transport_requests', function (Blueprint $table): void {
            $table->comment('Public transport enquiry contract without automatic pricing, GPS or dispatch workflow');
            $table->id()->comment('Internal primary key of the transport request');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the request safely');
            $table->string('status', 24)->default('new')->index()->comment('Request workflow status: new, reviewing, contacted, closed, or cancelled');
            $table->string('pickup_location')->comment('User-provided pickup location description');
            $table->string('delivery_location')->comment('User-provided delivery location description');
            $table->text('cargo_description')->comment('User-provided cargo description');
            $table->decimal('cargo_weight', 12, 3)->nullable()->comment('Optional estimated cargo weight supplied by the requester');
            $table->string('weight_unit', 16)->nullable()->comment('Unit of the supplied cargo weight');
            $table->foreignId('vehicle_type_id')->nullable()->comment('Optional preferred vehicle type')->constrained('hongvan_vehicle_types', 'id', 'hv_treq_vehicle_type_fk')->nullOnDelete();
            $table->date('requested_date')->nullable()->index()->comment('Optional requested transport date, not a confirmed dispatch date');
            $table->string('contact_name')->comment('Requester contact name');
            $table->string('contact_phone', 32)->comment('Requester contact phone');
            $table->string('contact_email')->nullable()->comment('Optional requester contact email');
            $table->string('ip_hash', 64)->nullable()->index()->comment('Privacy-preserving hash of the submission IP for abuse review');
            $table->string('user_agent_hash', 64)->nullable()->comment('Privacy-preserving hash of the user agent for abuse review');
            $table->timestamp('created_at')->nullable()->index()->comment('UTC time when the request was submitted');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the request was most recently updated');
        });
        Schema::create('hongvan_transport_request_status_histories', function (Blueprint $table): void {
            $table->comment('Append-only status history for transport enquiries');
            $table->id()->comment('Internal primary key of the status history entry');
            $table->foreignId('transport_request_id')->comment('Transport request whose status changed')->constrained('hongvan_transport_requests', 'id', 'hv_trsh_request_fk')->cascadeOnDelete();
            $table->string('from_status', 24)->nullable()->comment('Previous request status, or null for initial submission');
            $table->string('to_status', 24)->comment('New request status');
            $table->text('note')->nullable()->comment('Optional internal status change note');
            $table->foreignId('changed_by')->nullable()->comment('Administrator who changed the status, or null for public submission')->constrained('hongvan_users', 'id', 'hv_trsh_changed_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->index()->comment('UTC time when the status change occurred');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_transport_request_status_histories');
        Schema::dropIfExists('hongvan_transport_requests');
        Schema::dropIfExists('hongvan_transport_service_area_translations');
        Schema::dropIfExists('hongvan_transport_service_areas');
        Schema::dropIfExists('hongvan_transport_route_translations');
        Schema::dropIfExists('hongvan_transport_routes');
        Schema::dropIfExists('hongvan_vehicle_media');
        Schema::dropIfExists('hongvan_vehicle_translations');
        Schema::dropIfExists('hongvan_vehicles');
        Schema::dropIfExists('hongvan_vehicle_type_translations');
        Schema::dropIfExists('hongvan_vehicle_types');
    }
};
