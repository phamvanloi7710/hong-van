<?php

namespace Tests\Feature\Dashboard;

use App\Models\Lead;
use App\Models\Permission;
use App\Models\User;
use App\Notifications\LeadReceivedNotification;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_dashboard_requires_permission_and_scopes_leads_to_the_assigned_user_and_date_range(): void
    {
        $viewer = User::factory()->create();
        $other = User::factory()->create();
        $mine = $this->lead($viewer, now('UTC')->subDay());
        $mine->forceFill(['next_follow_up_at' => now('UTC')->subHour()])->save();
        $this->lead($other, now('UTC')->subDay());
        $this->lead($viewer, now('UTC')->subDays(60));

        $this->actingAs($viewer)->getJson('/api/admin/v1/dashboard')->assertForbidden();
        $this->allow($viewer, 'dashboard.view', 'leads.view');

        $this->getJson('/api/admin/v1/dashboard?from='.now()->subDays(7)->toDateString().'&to='.now()->toDateString().'&timezone=Asia%2FHo_Chi_Minh')
            ->assertOk()
            ->assertJsonPath('data.capabilities.leads', true)
            ->assertJsonPath('data.capabilities.products', false)
            ->assertJsonPath('data.cards.products', null)
            ->assertJsonPath('data.cards.leads.total', 2)
            ->assertJsonPath('data.cards.leads.new_in_range', 1)
            ->assertJsonPath('data.cards.leads.overdue_follow_up', 1);

        $this->getJson('/api/admin/v1/dashboard?from=2024-01-01&to=2026-01-02')->assertUnprocessable();
    }

    public function test_notification_center_is_per_user_and_rejects_unsafe_deep_links(): void
    {
        $viewer = User::factory()->create();
        $other = User::factory()->create();
        $this->allow($viewer, 'dashboard.view');
        $this->allow($other, 'dashboard.view');
        $lead = $this->lead($viewer, now('UTC'));
        $viewer->notify(new LeadReceivedNotification($lead));
        $unsafe = $viewer->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'UnsafeNotification',
            'data' => ['url' => 'https://attacker.example/admin/leads'],
        ]);
        $otherNotification = $other->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'OtherNotification',
            'data' => ['url' => '/admin/dashboard'],
        ]);

        $response = $this->actingAs($viewer)
            ->getJson('/api/admin/v1/dashboard/notifications?state=unread')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 2)
            ->assertJsonCount(2, 'data.items');
        $deepLinks = collect($response->json('data.items'))->pluck('deep_link')->all();
        $this->assertContains(null, $deepLinks);
        $this->assertContains('/admin/leads', $deepLinks);

        $this->postJson('/api/admin/v1/dashboard/notifications/'.$otherNotification->id.'/read')->assertNotFound();
        $this->postJson('/api/admin/v1/dashboard/notifications/'.$unsafe->id.'/read')->assertOk()->assertJsonPath('data.deep_link', null);
        $this->postJson('/api/admin/v1/dashboard/notifications/read-all')->assertOk()->assertJsonPath('data.unread_count', 0);
    }

    private function lead(User $assignee, \DateTimeInterface $createdAt): Lead
    {
        $lead = Lead::query()->create([
            'type' => 'contact',
            'status' => 'new',
            'source' => 'dashboard_test',
            'contact_name' => 'Dashboard lead',
            'contact_phone' => '0900000000',
            'contact_email' => 'dashboard@example.test',
            'original_payload' => ['message' => 'Dashboard test'],
            'consent_at' => $createdAt,
            'privacy_policy_version' => 'test',
            'assigned_to' => $assignee->getKey(),
            'retention_until' => now('UTC')->addYear(),
        ]);
        $lead->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->saveQuietly();

        return $lead;
    }

    private function allow(User $user, string ...$keys): void
    {
        foreach ($keys as $key) {
            $permission = Permission::query()->where('key', $key)->firstOrFail();
            $user->permissionOverrides()->attach($permission, ['is_allowed' => true]);
        }
    }
}
