<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_leads', function (Blueprint $table): void {
            $table->comment('Unified immutable public enquiry record and mutable internal workflow state');
            $table->id()->comment('Internal primary key of the lead');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the lead safely');
            $table->string('type', 24)->index()->comment('Lead type: contact, product_quote, transport, or warehouse');
            $table->string('status', 24)->default('new')->index()->comment('Workflow status: new, contacted, qualified, processing, done, spam, or archived');
            $table->string('source', 64)->default('public_api')->comment('Submission source identifier used for reporting');
            $table->text('contact_name')->comment('Encrypted original requester name');
            $table->text('contact_phone')->nullable()->comment('Encrypted original requester phone number');
            $table->text('contact_email')->nullable()->comment('Encrypted original requester email address');
            $table->longText('original_payload')->comment('Encrypted immutable validated submission snapshot');
            $table->string('idempotency_key_hash', 64)->nullable()->unique('hv_leads_idempotency_uniq')->comment('HMAC of the optional Idempotency-Key header');
            $table->string('dedupe_hash', 64)->nullable()->unique('hv_leads_dedupe_uniq')->comment('HMAC of canonical submission content during the deduplication window');
            $table->string('ip_hash', 64)->nullable()->index()->comment('Privacy-preserving HMAC of the submission IP for abuse review');
            $table->string('user_agent_hash', 64)->nullable()->comment('Privacy-preserving HMAC of the submission user agent');
            $table->timestamp('consent_at')->comment('UTC time when privacy/contact consent was accepted');
            $table->string('privacy_policy_version', 32)->comment('Version of the privacy policy accepted by the requester');
            $table->foreignId('assigned_to')->nullable()->index()->comment('Current administrator responsible for the lead')->constrained('hongvan_users', 'id', 'hv_leads_assigned_fk')->nullOnDelete();
            $table->timestamp('first_contacted_at')->nullable()->comment('UTC time when the lead first moved to contacted');
            $table->timestamp('resolved_at')->nullable()->comment('UTC time when the lead moved to done, spam, or archived');
            $table->timestamp('retention_until')->index()->comment('UTC time after which personal data may be anonymized');
            $table->timestamp('anonymized_at')->nullable()->comment('UTC time when personal data was anonymized under retention policy');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the lead was submitted');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when workflow state was most recently updated');
            $table->index(['type', 'status', 'created_at'], 'hv_leads_inbox_idx');
        });

        Schema::create('hongvan_lead_contact_details', function (Blueprint $table): void {
            $table->comment('Immutable detail record for a general contact submission');
            $table->id()->comment('Internal primary key of the contact detail');
            $table->foreignId('lead_id')->unique()->comment('Parent contact lead')->constrained('hongvan_leads', 'id', 'hv_lcd_lead_fk')->cascadeOnDelete();
            $table->text('company')->nullable()->comment('Encrypted optional company name supplied by the requester');
            $table->text('subject')->nullable()->comment('Encrypted optional contact subject');
            $table->longText('message')->comment('Encrypted original contact message');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the contact detail was created');
        });

        Schema::create('hongvan_lead_quote_items', function (Blueprint $table): void {
            $table->comment('Immutable product lines included in a product quote request');
            $table->id()->comment('Internal primary key of the quote item');
            $table->foreignId('lead_id')->index()->comment('Parent product quote lead')->constrained('hongvan_leads', 'id', 'hv_lqi_lead_fk')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->comment('Referenced catalog product when it still exists')->constrained('hongvan_products', 'id', 'hv_lqi_product_fk')->nullOnDelete();
            $table->string('product_name_snapshot')->comment('Immutable product name captured at submission time');
            $table->decimal('quantity', 14, 3)->nullable()->comment('Optional requested quantity without pricing semantics');
            $table->string('unit', 32)->nullable()->comment('Optional requester-provided quantity unit');
            $table->text('notes')->nullable()->comment('Encrypted optional note for this requested product');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the quote item was created');
        });

        Schema::create('hongvan_lead_request_links', function (Blueprint $table): void {
            $table->comment('One-to-one mapping from a unified lead to a transport or warehouse request');
            $table->id()->comment('Internal primary key of the request link');
            $table->foreignId('lead_id')->unique()->comment('Unified lead owning the linked request')->constrained('hongvan_leads', 'id', 'hv_lrl_lead_fk')->cascadeOnDelete();
            $table->foreignId('transport_request_id')->nullable()->unique()->comment('Linked transport request when lead type is transport')->constrained('hongvan_transport_requests', 'id', 'hv_lrl_transport_fk')->cascadeOnDelete();
            $table->foreignId('warehouse_request_id')->nullable()->unique()->comment('Linked warehouse request when lead type is warehouse')->constrained('hongvan_warehouse_requests', 'id', 'hv_lrl_warehouse_fk')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the link was created');
        });

        Schema::create('hongvan_lead_assignments', function (Blueprint $table): void {
            $table->comment('Append-only history of lead responsibility assignments');
            $table->id()->comment('Internal primary key of the assignment event');
            $table->foreignId('lead_id')->index()->comment('Lead whose responsibility changed')->constrained('hongvan_leads', 'id', 'hv_la_lead_fk')->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->comment('Previously assigned administrator')->constrained('hongvan_users', 'id', 'hv_la_from_fk')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->comment('Newly assigned administrator, or null when unassigned')->constrained('hongvan_users', 'id', 'hv_la_to_fk')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->comment('Administrator who performed the assignment')->constrained('hongvan_users', 'id', 'hv_la_actor_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->index()->comment('UTC time when responsibility changed');
        });

        Schema::create('hongvan_lead_status_histories', function (Blueprint $table): void {
            $table->comment('Append-only timeline of validated lead status transitions');
            $table->id()->comment('Internal primary key of the status event');
            $table->foreignId('lead_id')->index()->comment('Lead whose status changed')->constrained('hongvan_leads', 'id', 'hv_lsh_lead_fk')->cascadeOnDelete();
            $table->string('from_status', 24)->nullable()->comment('Previous status, or null for initial submission');
            $table->string('to_status', 24)->comment('New status after an allowed transition');
            $table->foreignId('changed_by')->nullable()->comment('Administrator who changed status, or null for submission')->constrained('hongvan_users', 'id', 'hv_lsh_actor_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->index()->comment('UTC time when status changed');
        });

        Schema::create('hongvan_lead_notes', function (Blueprint $table): void {
            $table->comment('Append-only encrypted internal notes for lead handling');
            $table->id()->comment('Internal primary key of the note');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the note safely');
            $table->foreignId('lead_id')->index()->comment('Lead receiving the internal note')->constrained('hongvan_leads', 'id', 'hv_ln_lead_fk')->cascadeOnDelete();
            $table->longText('body')->comment('Encrypted internal note body');
            $table->foreignId('created_by')->nullable()->comment('Administrator who authored the note')->constrained('hongvan_users', 'id', 'hv_ln_actor_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->index()->comment('UTC time when the note was authored');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_lead_notes');
        Schema::dropIfExists('hongvan_lead_status_histories');
        Schema::dropIfExists('hongvan_lead_assignments');
        Schema::dropIfExists('hongvan_lead_request_links');
        Schema::dropIfExists('hongvan_lead_quote_items');
        Schema::dropIfExists('hongvan_lead_contact_details');
        Schema::dropIfExists('hongvan_leads');
    }
};
