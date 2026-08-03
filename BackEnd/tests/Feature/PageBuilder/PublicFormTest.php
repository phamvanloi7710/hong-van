<?php

namespace Tests\Feature\PageBuilder;

use App\Domain\PageBuilder\FormContextSigner;
use App\Jobs\Leads\DispatchLeadNotifications;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class PublicFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        RateLimiter::clear('public-form|127.0.0.1');
    }

    public function test_web_contact_form_redirects_with_success_and_deduplicates_by_hidden_key(): void
    {
        $payload = $this->contactPayload();

        $this->from('/')->post('/forms/contact', $payload)
            ->assertStatus(303)
            ->assertRedirect('/')
            ->assertSessionHas('page_builder_form_status.form_type', 'contact')
            ->assertSessionHas('page_builder_form_status.block_id', 'form-contact-01');
        $this->from('/')->post('/forms/contact', $payload)
            ->assertStatus(303)
            ->assertRedirect('/');

        $this->assertDatabaseCount('hongvan_leads', 1);
        $this->assertDatabaseCount('hongvan_lead_contact_details', 1);
        Queue::assertPushed(DispatchLeadNotifications::class, 1);
    }

    public function test_web_form_rejects_missing_consent_honeypot_and_invalid_contract(): void
    {
        $this->from('/')->post('/forms/contact', [...$this->contactPayload(), 'consent' => false])
            ->assertRedirect('/')
            ->assertSessionHasErrors('consent');
        $this->from('/')->post('/forms/contact', [...$this->contactPayload(), 'website' => 'spam'])
            ->assertRedirect('/')
            ->assertSessionHasErrors('website');
        $this->from('/')->post('/forms/contact', [...$this->contactPayload(), '_form_definition' => 'contact@99'])
            ->assertRedirect('/')
            ->assertSessionHasErrors('_form_definition');

        $this->assertDatabaseCount('hongvan_leads', 0);
        Queue::assertNothingPushed();
    }

    public function test_product_quote_rejects_tampered_or_mismatched_hidden_context(): void
    {
        $product = Product::factory()->published()->create();
        $other = Product::factory()->published()->create();
        $token = app(FormContextSigner::class)->sign('product_quote', 'form-product-quote-01', 'product', $product->public_id);
        $payload = $this->quotePayload($other->public_id, $token);

        $this->from('/')->post('/forms/quote', $payload)
            ->assertRedirect('/')
            ->assertSessionHasErrors('items.0.product_id');
        $this->from('/')->post('/forms/quote', [...$this->quotePayload($product->public_id, $token), 'form_context_token' => $token.'tampered'])
            ->assertRedirect('/')
            ->assertSessionHasErrors('form_context_token');
        $this->from('/')->post('/forms/quote', [...$this->quotePayload($product->public_id, $token), '_block_id' => 'form-product-quote-02'])
            ->assertRedirect('/')
            ->assertSessionHasErrors('_block_id');

        $this->assertDatabaseCount('hongvan_leads', 0);
        Queue::assertNothingPushed();
    }

    public function test_product_quote_accepts_signed_product_context_and_queues_notification(): void
    {
        $product = Product::factory()->published()->create();
        $product->translations()->create(['locale' => 'vi', 'name' => 'Sản phẩm báo giá', 'slug' => 'san-pham-bao-gia']);
        $token = app(FormContextSigner::class)->sign('product_quote', 'form-product-quote-01', 'product', $product->public_id);

        $this->from('/')->post('/forms/quote', $this->quotePayload($product->public_id, $token))
            ->assertStatus(303)
            ->assertRedirect('/')
            ->assertSessionHas('page_builder_form_status.form_type', 'product_quote');

        $this->assertDatabaseCount('hongvan_leads', 1);
        $this->assertDatabaseCount('hongvan_lead_quote_items', 1);
        Queue::assertPushed(DispatchLeadNotifications::class, 1);
    }

    public function test_public_form_web_endpoint_is_rate_limited(): void
    {
        config()->set('security.rate_limits.public_forms_per_minute', 2);

        $first = $this->contactPayload();
        $second = [...$first, '_idempotency_key' => '10000000-0000-4000-8000-000000000002', 'message' => 'Yêu cầu thứ hai.'];
        $third = [...$first, '_idempotency_key' => '10000000-0000-4000-8000-000000000003', 'message' => 'Yêu cầu thứ ba.'];

        $this->from('/')->post('/forms/contact', $first)->assertStatus(303);
        $this->from('/')->post('/forms/contact', $second)->assertStatus(303);
        $this->from('/')->post('/forms/contact', $third)->assertStatus(429);

        $this->assertDatabaseCount('hongvan_leads', 2);
        Queue::assertPushed(DispatchLeadNotifications::class, 2);
    }

    /** @return array<string, mixed> */
    private function contactPayload(): array
    {
        return [
            '_form_definition' => 'contact@1',
            '_block_id' => 'form-contact-01',
            '_idempotency_key' => '10000000-0000-4000-8000-000000000001',
            'contact_name' => 'Khách hàng',
            'contact_phone' => '0900000000',
            'contact_email' => 'customer@example.test',
            'subject' => 'Cần tư vấn',
            'message' => 'Vui lòng liên hệ lại.',
            'consent' => true,
            'privacy_policy_version' => config('leads.privacy_policy_version'),
            'website' => '',
        ];
    }

    /** @return array<string, mixed> */
    private function quotePayload(string $productId, string $token): array
    {
        return [
            '_form_definition' => 'product_quote@1',
            '_block_id' => 'form-product-quote-01',
            '_idempotency_key' => '20000000-0000-4000-8000-000000000001',
            'form_context_token' => $token,
            'contact_name' => 'Khách báo giá',
            'contact_phone' => '0900000001',
            'items' => [['product_id' => $productId, 'quantity' => 10, 'unit' => 'bao']],
            'consent' => true,
            'privacy_policy_version' => config('leads.privacy_policy_version'),
            'website' => '',
        ];
    }
}
