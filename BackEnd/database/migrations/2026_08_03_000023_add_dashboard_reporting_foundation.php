<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hongvan_leads', function (Blueprint $table): void {
            $table->timestamp('next_follow_up_at')->nullable()->after('first_contacted_at')->index()->comment('UTC time when the assigned administrator should next follow up this lead');
        });

        Schema::create('hongvan_report_exports', function (Blueprint $table): void {
            $table->comment('Private permission-scoped report export requests and generated files');
            $table->id()->comment('Internal primary key of the report export');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to reference the report export safely');
            $table->foreignId('requested_by')->index()->comment('Administrator who requested and exclusively owns the export')->constrained('hongvan_users', 'id', 'hv_report_exports_actor_fk')->cascadeOnDelete();
            $table->string('type', 40)->index()->comment('Allowlisted report type used to select the server-side generator');
            $table->string('status', 24)->default('queued')->index()->comment('Generation state: queued, processing, ready, or failed');
            $table->json('filters')->nullable()->comment('Validated date range and allowlisted report filters captured at request time');
            $table->unsignedInteger('row_count')->default(0)->comment('Number of scoped data rows included in the generated report');
            $table->string('disk', 32)->default('local')->comment('Configured private filesystem disk containing the generated report');
            $table->string('file_path')->nullable()->comment('Private storage path of the generated CSV when status is ready');
            $table->text('failure_message')->nullable()->comment('Sanitized internal failure summary without credentials or exported personal data');
            $table->timestamp('expires_at')->index()->comment('UTC time after which the private report file must no longer be downloaded');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the export was requested');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when generation state last changed');
            $table->index(['requested_by', 'status', 'created_at'], 'hv_report_exports_owner_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_report_exports');

        Schema::table('hongvan_leads', function (Blueprint $table): void {
            $table->dropColumn('next_follow_up_at');
        });
    }
};
