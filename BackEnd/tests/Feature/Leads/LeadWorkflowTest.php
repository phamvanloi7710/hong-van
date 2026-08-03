<?php

namespace Tests\Feature\Leads;

use App\Domain\Identity\PermissionRegistry;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use LogicException;
use Tests\TestCase;

final class LeadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        Queue::fake();
    }

    public function test_permissions_transitions_assignments_notes_timeline_metrics_and_export_are_enforced(): void
    {
        $leadId = $this->postJson('/api/public/v1/contact-requests', $this->payload())->assertCreated()->json('data.public_id');
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/admin/v1/leads')->assertForbidden();

        $admin = $this->superAdmin();
        $assignee = User::factory()->create();
        $this->actingAs($admin);
        $this->getJson('/api/admin/v1/leads?type=contact&status=new')->assertOk()->assertJsonPath('data.meta.total', 1);
        $this->postJson('/api/admin/v1/leads/'.$leadId.'/status', ['status' => 'done'])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->postJson('/api/admin/v1/leads/'.$leadId.'/assign', ['user_id' => $assignee->public_id])->assertOk()->assertJsonPath('data.assignee.public_id', $assignee->public_id);
        $this->postJson('/api/admin/v1/leads/'.$leadId.'/status', ['status' => 'contacted'])->assertOk()->assertJsonPath('data.status', 'contacted');
        $this->postJson('/api/admin/v1/leads/'.$leadId.'/notes', ['body' => 'Internal follow-up only'])->assertCreated()->assertJsonPath('data.notes.0.body', 'Internal follow-up only');
        $this->getJson('/api/admin/v1/leads/'.$leadId)->assertOk()->assertJsonCount(2, 'data.timeline')->assertJsonCount(1, 'data.assignments')->assertJsonPath('data.original_payload.message', 'Please call me.');
        $this->getJson('/api/admin/v1/leads/metrics')->assertOk()->assertJsonPath('data.total', 1);
        $this->get('/api/admin/v1/leads/export')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertDatabaseCount('hongvan_lead_assignments', 1);
        $this->assertDatabaseCount('hongvan_lead_notes', 1);
        $this->assertDatabaseHas('hongvan_audit_logs', ['action' => 'lead.note.added', 'subject_public_id' => $leadId]);
    }

    public function test_original_submission_and_internal_notes_are_immutable(): void
    {
        $leadId = $this->postJson('/api/public/v1/contact-requests', $this->payload())->assertCreated()->json('data.public_id');
        $lead = Lead::query()->where('public_id', $leadId)->firstOrFail();

        $this->expectException(LogicException::class);
        $lead->forceFill(['contact_name' => 'Changed'])->save();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['contact_name' => 'Lead customer', 'contact_phone' => '0900000003', 'contact_email' => 'lead@example.test', 'message' => 'Please call me.', 'consent' => true, 'privacy_policy_version' => config('leads.privacy_policy_version')];
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', PermissionRegistry::SUPER_ADMIN_ROLE)->firstOrFail();
        $user->roles()->attach($role, ['created_at' => now('UTC')]);

        return $user;
    }
}
