<?php

namespace Tests\Feature\Leads;

use App\Jobs\Leads\DispatchLeadNotifications;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class PublicSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('public-form|127.0.0.1');
    }

    public function test_contact_submission_requires_consent_rejects_honeypot_and_deduplicates_without_blocking_on_notifications(): void
    {
        Queue::fake();
        $payload = $this->contactPayload();

        $this->postJson('/api/public/v1/contact-requests', $payload)
            ->assertCreated()->assertJsonPath('data.type', 'contact')->assertJsonPath('data.duplicate', false);
        $this->postJson('/api/public/v1/contact-requests', $payload)
            ->assertOk()->assertJsonPath('data.duplicate', true);

        $this->assertDatabaseCount('hongvan_leads', 1);
        $this->assertDatabaseCount('hongvan_lead_contact_details', 1);
        $this->assertDatabaseCount('hongvan_lead_status_histories', 1);
        $this->assertDatabaseMissing('hongvan_leads', ['contact_email' => 'customer@example.test']);
        Queue::assertPushed(DispatchLeadNotifications::class, 1);

        $this->postJson('/api/public/v1/contact-requests', [...$payload, 'message' => 'Another message', 'website' => 'spam'])
            ->assertUnprocessable()->assertJsonValidationErrors('website');
        $this->postJson('/api/public/v1/contact-requests', [...$payload, 'message' => 'No consent', 'consent' => false])
            ->assertUnprocessable()->assertJsonValidationErrors('consent');
    }

    public function test_product_quote_and_transport_request_create_unified_leads_and_mapping(): void
    {
        Queue::fake();
        $product = Product::factory()->published()->create();
        $product->translations()->create(['locale' => 'vi', 'name' => 'Phân bón mẫu', 'slug' => 'phan-bon-mau']);

        $this->postJson('/api/public/v1/quote-requests', [
            'contact_name' => 'Quote customer', 'contact_phone' => '0900000001',
            'items' => [['product_id' => $product->public_id, 'quantity' => 10, 'unit' => 'bao']],
            ...$this->consent(),
        ])->assertCreated()->assertJsonPath('data.type', 'product_quote');

        $this->postJson('/api/public/v1/transport-requests', [
            'pickup_location' => 'Kho A', 'delivery_location' => 'Kho B', 'cargo_description' => 'Phân bón đóng bao',
            'contact_name' => 'Transport customer', 'contact_phone' => '0900000002', ...$this->consent(),
        ])->assertCreated()->assertJsonPath('data.type', 'transport')->assertJsonPath('data.status', 'new');

        $this->assertDatabaseCount('hongvan_leads', 2);
        $this->assertDatabaseCount('hongvan_lead_quote_items', 1);
        $this->assertDatabaseCount('hongvan_transport_requests', 1);
        $this->assertDatabaseCount('hongvan_lead_request_links', 1);
        Queue::assertPushed(DispatchLeadNotifications::class, 2);
    }

    public function test_public_form_rate_limit_is_shared_across_lead_types(): void
    {
        Queue::fake();
        for ($index = 0; $index < 10; $index++) {
            $this->postJson('/api/public/v1/contact-requests', [...$this->contactPayload(), 'message' => 'Message '.$index])->assertCreated();
        }
        $this->postJson('/api/public/v1/contact-requests', [...$this->contactPayload(), 'message' => 'Rate limited'])->assertStatus(429);
    }

    /** @return array<string, mixed> */
    private function contactPayload(): array
    {
        return ['contact_name' => 'Customer', 'contact_phone' => '0900000000', 'contact_email' => 'customer@example.test', 'subject' => 'Need information', 'message' => 'Please contact me.', ...$this->consent()];
    }

    /** @return array<string, mixed> */
    private function consent(): array
    {
        return ['consent' => true, 'privacy_policy_version' => config('leads.privacy_policy_version')];
    }
}
