<?php

namespace App\Domain\Leads;

use App\Domain\Transportation\TransportationManager;
use App\Domain\Warehouses\WarehouseManager;
use App\Jobs\Leads\DispatchLeadNotifications;
use App\Models\Lead;
use App\Models\Product;
use App\Models\TransportRequest;
use App\Models\WarehouseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class LeadIntakeService
{
    public function __construct(
        private TransportationManager $transportation,
        private WarehouseManager $warehouses,
    ) {}

    /** @param array<string, mixed> $data */
    public function contact(array $data, Request $request): LeadSubmission
    {
        return $this->submit('contact', $data, $request, function (Lead $lead) use ($data): null {
            $lead->contactDetail()->create([
                ...Arr::only($data, ['company', 'subject', 'message']),
                'created_at' => now('UTC'),
            ]);

            return null;
        });
    }

    /** @param array<string, mixed> $data */
    public function quote(array $data, Request $request): LeadSubmission
    {
        return $this->submit('product_quote', $data, $request, function (Lead $lead) use ($data): null {
            foreach ($data['items'] as $item) {
                $product = Product::query()->with('translations')->where('public_id', $item['product_id'])->firstOrFail();
                $name = (string) ($product->translationForLocale('vi')?->getAttribute('name') ?? $product->translations->first()->name ?? $product->sku);
                $lead->quoteItems()->create([
                    'product_id' => $product->getKey(),
                    'product_name_snapshot' => $name,
                    'quantity' => $item['quantity'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'created_at' => now('UTC'),
                ]);
            }
            if (isset($data['message']) && is_string($data['message']) && $data['message'] !== '') {
                $lead->contactDetail()->create(['message' => $data['message'], 'created_at' => now('UTC')]);
            }

            return null;
        });
    }

    /** @param array<string, mixed> $data */
    public function transport(array $data, Request $request): LeadSubmission
    {
        return $this->submit('transport', $data, $request, function (Lead $lead) use ($data, $request): TransportRequest {
            $transport = $this->transportation->createRequest([...$data, 'contact_name' => 'See linked lead', 'contact_phone' => 'See linked lead', 'contact_email' => null], $request->ip(), $request->userAgent());
            $lead->requestLink()->create(['transport_request_id' => $transport->getKey(), 'created_at' => now('UTC')]);

            return $transport;
        });
    }

    /** @param array<string, mixed> $data */
    public function warehouse(array $data, Request $request): LeadSubmission
    {
        return $this->submit('warehouse', $data, $request, function (Lead $lead) use ($data, $request): WarehouseRequest {
            $warehouse = $this->warehouses->createRequest([...$data, 'contact_name' => 'See linked lead', 'contact_phone' => 'See linked lead', 'contact_email' => null], $request->ip(), $request->userAgent());
            $lead->requestLink()->create(['warehouse_request_id' => $warehouse->getKey(), 'created_at' => now('UTC')]);

            return $warehouse;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  callable(Lead):(TransportRequest|WarehouseRequest|null)  $persistDetails
     */
    private function submit(string $type, array $data, Request $request, callable $persistDetails): LeadSubmission
    {
        $payload = Arr::except($data, ['website']);
        $idempotency = trim((string) $request->header('Idempotency-Key', ''));
        if (mb_strlen($idempotency) > 200) {
            throw ValidationException::withMessages(['idempotency_key' => [__('leads.invalid_idempotency_key')]]);
        }

        $idempotencyHash = $idempotency === '' ? null : $this->hash($idempotency);
        $dedupeHash = $this->hash($type.'|'.$this->canonicalJson($payload));
        $existing = $idempotencyHash === null ? null : Lead::query()->where('idempotency_key_hash', $idempotencyHash)->first();
        $existing ??= Lead::query()
            ->where('dedupe_hash', $dedupeHash)
            ->where('created_at', '>=', now('UTC')->subMinutes(max(1, (int) config('leads.deduplicate_minutes', 15))))
            ->first();

        if ($existing instanceof Lead) {
            return new LeadSubmission($existing, $this->linkedRequest($existing), false);
        }

        Lead::query()->where('dedupe_hash', $dedupeHash)
            ->where('created_at', '<', now('UTC')->subMinutes(max(1, (int) config('leads.deduplicate_minutes', 15))))
            ->update(['dedupe_hash' => null]);

        $submission = DB::transaction(function () use ($data, $dedupeHash, $idempotencyHash, $payload, $persistDetails, $request, $type): LeadSubmission {
            $lead = Lead::query()->create([
                'type' => $type,
                'status' => 'new',
                'source' => 'public_api',
                'contact_name' => $data['contact_name'],
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'original_payload' => $payload,
                'idempotency_key_hash' => $idempotencyHash,
                'dedupe_hash' => $dedupeHash,
                'ip_hash' => $request->ip() ? $this->hash((string) $request->ip()) : null,
                'user_agent_hash' => $request->userAgent() ? $this->hash((string) $request->userAgent()) : null,
                'consent_at' => now('UTC'),
                'privacy_policy_version' => (string) ($data['privacy_policy_version'] ?? config('leads.privacy_policy_version')),
                'retention_until' => now('UTC')->addDays(max(1, (int) config('leads.retention_days', 365))),
            ]);
            $lead->statusHistories()->create(['from_status' => null, 'to_status' => 'new', 'created_at' => now('UTC')]);
            $detail = $persistDetails($lead);

            return new LeadSubmission($lead, $detail, true);
        });

        DispatchLeadNotifications::dispatch($submission->lead->getKey())->afterCommit();

        return $submission;
    }

    private function linkedRequest(Lead $lead): TransportRequest|WarehouseRequest|null
    {
        $link = $lead->requestLink()->with(['transportRequest', 'warehouseRequest'])->first();
        if ($link === null) {
            return null;
        }

        return $link->transportRequest ?? $link->warehouseRequest;
    }

    private function hash(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    /** @param array<string, mixed> $payload */
    private function canonicalJson(array $payload): string
    {
        $sort = function (mixed $value) use (&$sort): mixed {
            if (! is_array($value)) {
                return is_string($value) ? trim($value) : $value;
            }
            if (array_is_list($value)) {
                return array_map($sort, $value);
            }
            ksort($value);

            return array_map($sort, $value);
        };

        return json_encode($sort($payload), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
