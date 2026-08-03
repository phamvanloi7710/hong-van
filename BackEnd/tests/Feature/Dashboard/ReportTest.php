<?php

namespace Tests\Feature\Dashboard;

use App\Jobs\Dashboard\GenerateLeadReportExport;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_small_report_is_generated_synchronously_scoped_and_csv_safe(): void
    {
        config()->set('dashboard.sync_export_limit', 10);
        $viewer = User::factory()->create();
        $other = User::factory()->create();
        $this->allow($viewer, 'dashboard.view', 'leads.view', 'leads.export');
        $this->lead($viewer, '=2+3');
        $this->lead($other, 'Must not leak');

        $payload = ['from' => now()->subDay()->toDateString(), 'to' => now()->toDateString(), 'timezone' => 'Asia/Ho_Chi_Minh'];
        $response = $this->actingAs($viewer)->postJson('/api/admin/v1/dashboard/reports/leads', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'ready')
            ->assertJsonPath('data.row_count', 1);
        $publicId = $response->json('data.public_id');
        $contents = $this->get('/api/admin/v1/dashboard/reports/'.$publicId.'/download')->assertOk()->streamedContent();

        $this->assertStringContainsString("'=2+3", $contents);
        $this->assertStringNotContainsString('Must not leak', $contents);
        $this->allow($other, 'dashboard.view', 'leads.view', 'leads.export');
        $this->actingAs($other)->getJson('/api/admin/v1/dashboard/reports/'.$publicId)->assertNotFound();
    }

    public function test_large_report_is_queued_instead_of_generated_in_request(): void
    {
        Queue::fake();
        config()->set('dashboard.sync_export_limit', 1);
        $viewer = User::factory()->create();
        $this->allow($viewer, 'dashboard.view', 'leads.view', 'leads.export');
        $this->lead($viewer, 'First');
        $this->lead($viewer, 'Second');

        $this->actingAs($viewer)->postJson('/api/admin/v1/dashboard/reports/leads', [
            'from' => now()->subDay()->toDateString(),
            'to' => now()->toDateString(),
        ])->assertStatus(202)->assertJsonPath('data.status', 'queued')->assertJsonPath('data.row_count', 2);

        Queue::assertPushed(GenerateLeadReportExport::class);
    }

    private function lead(User $assignee, string $name): Lead
    {
        return Lead::query()->create([
            'type' => 'contact',
            'status' => 'new',
            'source' => 'report_test',
            'contact_name' => $name,
            'contact_phone' => '0900000000',
            'contact_email' => 'report@example.test',
            'original_payload' => ['message' => 'Report test'],
            'consent_at' => now('UTC'),
            'privacy_policy_version' => 'test',
            'assigned_to' => $assignee->getKey(),
            'retention_until' => now('UTC')->addYear(),
        ]);
    }

    private function allow(User $user, string ...$keys): void
    {
        foreach ($keys as $key) {
            $permission = Permission::query()->where('key', $key)->firstOrFail();
            $user->permissionOverrides()->attach($permission, ['is_allowed' => true]);
        }
    }
}
