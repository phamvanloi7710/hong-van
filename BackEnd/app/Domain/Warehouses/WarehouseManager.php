<?php

namespace App\Domain\Warehouses;

use App\Domain\Audit\AuditTrail;
use App\Domain\Localization\TranslatableModel;
use App\Domain\Media\MediaUsageTracker;
use App\Exceptions\ConflictException;
use App\Models\Media;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseFacility;
use App\Models\WarehouseRequest;
use App\Models\WarehouseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final readonly class WarehouseManager
{
    public function __construct(private MediaUsageTracker $mediaUsage, private AuditTrail $audit) {}

    /** @param array<string,mixed> $data */
    public function saveFacility(User $actor, ?WarehouseFacility $facility, array $data): WarehouseFacility
    {
        $facility ??= new WarehouseFacility;
        $facility->fill([...Arr::only($data, ['code', 'icon', 'is_active', 'sort_order']), $facility->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->syncTranslations($facility, $data['translations']);
        $this->record($facility->wasRecentlyCreated ? 'warehouse_facility.created' : 'warehouse_facility.updated', $actor, $facility);
        $this->touchCache();

        return $facility->fresh('translations')->loadCount('warehouses');
    }

    /** @param array<string,mixed> $data */
    public function saveService(User $actor, ?WarehouseService $service, array $data): WarehouseService
    {
        $service ??= new WarehouseService;
        $service->fill([...Arr::only($data, ['code', 'icon', 'is_active', 'sort_order']), $service->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->syncTranslations($service, $data['translations']);
        $this->record($service->wasRecentlyCreated ? 'warehouse_service.created' : 'warehouse_service.updated', $actor, $service);
        $this->touchCache();

        return $service->fresh('translations')->loadCount('warehouses');
    }

    /** @param array<string,mixed> $data */
    public function saveWarehouse(User $actor, ?Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($actor, $warehouse, $data): Warehouse {
            $warehouse ??= new Warehouse;
            /** @var list<Media> $oldMedia */
            $oldMedia = $warehouse->exists ? $warehouse->media()->get()->all() : [];
            $warehouse->fill([
                ...Arr::only($data, ['code', 'area_value', 'area_unit', 'latitude', 'longitude', 'map_display', 'business_hours', 'status', 'is_featured', 'sort_order', 'published_at', 'unpublished_at']),
                $warehouse->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($warehouse, $data['translations']);
            $this->syncRelation($warehouse, 'facilities', WarehouseFacility::class, $data['facility_ids'] ?? []);
            $this->syncRelation($warehouse, 'services', WarehouseService::class, $data['service_ids'] ?? []);
            $this->syncMedia($warehouse, $oldMedia, $data['media'] ?? []);
            $this->record($warehouse->wasRecentlyCreated ? 'warehouse.created' : 'warehouse.updated', $actor, $warehouse);
            $this->touchCache();

            return $this->loadWarehouse($warehouse);
        });
    }

    public function publish(User $actor, Warehouse $warehouse): Warehouse
    {
        $warehouse->forceFill(['status' => 'published', 'published_at' => $warehouse->published_at ?? now('UTC'), 'unpublished_at' => null, 'updated_by' => $actor->getKey()])->save();
        $this->record('warehouse.published', $actor, $warehouse);
        $this->touchCache();

        return $this->loadWarehouse($warehouse);
    }

    public function deleteReference(User $actor, WarehouseFacility|WarehouseService $reference): void
    {
        if ($reference->warehouses()->exists()) {
            throw new ConflictException(__('warehouses.reference_in_use'));
        }
        $this->record($reference->getTable().'.deleted', $actor, $reference);
        $reference->delete();
        $this->touchCache();
    }

    public function deleteWarehouse(User $actor, Warehouse $warehouse): void
    {
        foreach ($warehouse->media as $media) {
            $this->mediaUsage->release($media, 'warehouse', $warehouse->public_id, 'media:'.$media->public_id);
        }
        $this->record('warehouse.deleted', $actor, $warehouse);
        $warehouse->forceFill(['deleted_by' => $actor->getKey()])->save();
        $warehouse->delete();
        $this->touchCache();
    }

    /** @param array<string,mixed> $data */
    public function createRequest(array $data, ?string $ip, ?string $userAgent): WarehouseRequest
    {
        return DB::transaction(function () use ($data, $ip, $userAgent): WarehouseRequest {
            $request = WarehouseRequest::query()->create([
                ...Arr::only($data, ['goods_description', 'required_area', 'area_unit', 'required_volume', 'volume_unit', 'duration_description', 'start_date', 'storage_requirements', 'preferred_location', 'contact_name', 'contact_phone', 'contact_email']),
                'status' => 'new', 'warehouse_id' => $this->id(Warehouse::class, $data['warehouse_id'] ?? null),
                'ip_hash' => $ip ? hash('sha256', $ip) : null, 'user_agent_hash' => $userAgent ? hash('sha256', $userAgent) : null,
            ]);
            $request->statusHistories()->create(['from_status' => null, 'to_status' => 'new']);

            return $request->fresh('preferredWarehouse.translations');
        });
    }

    /** @param list<array<string,mixed>> $items */
    private function syncTranslations(TranslatableModel $model, array $items): void
    {
        $locales = [];
        foreach ($items as $item) {
            $locales[] = $item['locale'];
            $model->translations()->updateOrCreate(['locale' => $item['locale']], Arr::except($item, ['locale']));
        }
        $model->translations()->whereNotIn('locale', $locales)->delete();
    }

    /**
     * @param  class-string<Model>  $class
     * @param  list<string>  $publicIds
     */
    private function syncRelation(Warehouse $warehouse, string $relation, string $class, array $publicIds): void
    {
        $ids = $class::query()->whereIn('public_id', $publicIds)->pluck('id')->all();
        $relationQuery = $relation === 'facilities' ? $warehouse->facilities() : $warehouse->services();
        $relationQuery->sync(collect($ids)->mapWithKeys(fn ($id, $index): array => [$id => ['sort_order' => $index, 'created_at' => now('UTC')]])->all());
    }

    /**
     * @param  list<Media>  $old
     * @param  list<array<string,mixed>>  $items
     */
    private function syncMedia(Warehouse $warehouse, array $old, array $items): void
    {
        $pivot = [];
        foreach ($items as $item) {
            $id = $this->id(Media::class, $item['media_id']);
            if ($id !== null) {
                $pivot[$id] = Arr::only($item, ['role', 'sort_order']);
            }
        }
        $warehouse->media()->sync($pivot);
        $new = $warehouse->media()->get();
        foreach ($old as $media) {
            if (! $new->contains('id', $media->getKey())) {
                $this->mediaUsage->release($media, 'warehouse', $warehouse->public_id, 'media:'.$media->public_id);
            }
        }
        foreach ($new as $media) {
            $this->mediaUsage->track($media, 'warehouse', $warehouse->public_id, 'media:'.$media->public_id);
        }
    }

    /** @param class-string<Model> $class */
    private function id(string $class, mixed $publicId): ?int
    {
        return is_string($publicId) && $publicId !== '' ? (int) $class::query()->where('public_id', $publicId)->valueOrFail('id') : null;
    }

    private function loadWarehouse(Warehouse $warehouse): Warehouse
    {
        return $warehouse->fresh(['translations', 'media', 'facilities.translations', 'services.translations']);
    }

    private function touchCache(): void
    {
        Cache::forever('warehouses:version', ((int) Cache::get('warehouses:version', 0)) + 1);
    }

    private function record(string $action, User $actor, Model $subject): void
    {
        $this->audit->record(action: $action, actor: $actor, subjectType: $subject->getTable(), subjectPublicId: (string) $subject->getAttribute('public_id'));
    }
}
