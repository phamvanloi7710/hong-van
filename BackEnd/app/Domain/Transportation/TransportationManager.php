<?php

namespace App\Domain\Transportation;

use App\Domain\Audit\AuditTrail;
use App\Domain\Localization\TranslatableModel;
use App\Domain\Media\MediaUsageTracker;
use App\Exceptions\ConflictException;
use App\Models\Media;
use App\Models\TransportRequest;
use App\Models\TransportRoute;
use App\Models\TransportServiceArea;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final readonly class TransportationManager
{
    public function __construct(private MediaUsageTracker $mediaUsage, private AuditTrail $audit) {}

    /** @param array<string, mixed> $data */
    public function saveVehicleType(User $actor, ?VehicleType $type, array $data): VehicleType
    {
        $type ??= new VehicleType;
        $type->fill([...Arr::only($data, ['code', 'is_active', 'sort_order']), $type->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->syncTranslations($type, $data['translations']);
        $this->record($type->wasRecentlyCreated ? 'vehicle_type.created' : 'vehicle_type.updated', $actor, $type);
        $this->touchCache();

        return $type->fresh('translations')->loadCount('vehicles');
    }

    public function deleteVehicleType(User $actor, VehicleType $type): void
    {
        if ($type->vehicles()->exists()) {
            throw new ConflictException(__('transportation.vehicle_type_in_use'));
        }
        $this->record('vehicle_type.deleted', $actor, $type);
        $type->delete();
        $this->touchCache();
    }

    /** @param array<string, mixed> $data */
    public function saveVehicle(User $actor, ?Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($actor, $vehicle, $data): Vehicle {
            $vehicle ??= new Vehicle;
            $oldMedia = $vehicle->exists ? $vehicle->media()->get()->all() : [];
            $vehicle->fill([
                ...Arr::only($data, ['code', 'payload_capacity', 'payload_unit', 'availability_display', 'status', 'is_featured', 'sort_order', 'published_at', 'unpublished_at']),
                'vehicle_type_id' => $this->id(VehicleType::class, $data['vehicle_type_id']),
                $vehicle->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($vehicle, $data['translations']);
            $this->syncMedia($vehicle, $oldMedia, $data['media'] ?? []);
            $this->record($vehicle->wasRecentlyCreated ? 'vehicle.created' : 'vehicle.updated', $actor, $vehicle);
            $this->touchCache();

            return $this->loadVehicle($vehicle);
        });
    }

    /** @param array<string, mixed> $data */
    public function saveRoute(User $actor, ?TransportRoute $route, array $data): TransportRoute
    {
        $route ??= new TransportRoute;
        $route->fill([...Arr::only($data, ['code', 'origin_code', 'destination_code', 'status', 'is_featured', 'sort_order', 'published_at', 'unpublished_at']), $route->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->syncTranslations($route, $data['translations']);
        $this->record($route->wasRecentlyCreated ? 'transport_route.created' : 'transport_route.updated', $actor, $route);
        $this->touchCache();

        return $route->fresh('translations');
    }

    /** @param array<string, mixed> $data */
    public function saveArea(User $actor, ?TransportServiceArea $area, array $data): TransportServiceArea
    {
        $area ??= new TransportServiceArea;
        $area->fill([...Arr::only($data, ['code', 'status', 'is_featured', 'sort_order', 'published_at', 'unpublished_at']), $area->exists ? 'updated_by' : 'created_by' => $actor->getKey(), 'updated_by' => $actor->getKey()])->save();
        $this->syncTranslations($area, $data['translations']);
        $this->record($area->wasRecentlyCreated ? 'transport_area.created' : 'transport_area.updated', $actor, $area);
        $this->touchCache();

        return $area->fresh('translations');
    }

    public function publish(User $actor, Model $model): Model
    {
        $model->forceFill(['status' => 'published', 'published_at' => $model->getAttribute('published_at') ?? now('UTC'), 'unpublished_at' => null, 'updated_by' => $actor->getKey()])->save();
        $this->record($model->getTable().'.published', $actor, $model);
        $this->touchCache();

        return $model->fresh(['translations']);
    }

    public function delete(User $actor, Model $model): void
    {
        if ($model instanceof Vehicle) {
            foreach ($model->media as $media) {
                $this->mediaUsage->release($media, 'vehicle', $model->public_id, 'media:'.$media->public_id);
            }
        }
        $this->record($model->getTable().'.deleted', $actor, $model);
        $model->delete();
        $this->touchCache();
    }

    /** @param array<string, mixed> $data */
    public function createRequest(array $data, ?string $ip, ?string $userAgent): TransportRequest
    {
        return DB::transaction(function () use ($data, $ip, $userAgent): TransportRequest {
            $request = TransportRequest::query()->create([
                ...Arr::only($data, ['pickup_location', 'delivery_location', 'cargo_description', 'cargo_weight', 'weight_unit', 'requested_date', 'contact_name', 'contact_phone', 'contact_email']),
                'status' => 'new', 'vehicle_type_id' => $this->id(VehicleType::class, $data['vehicle_type_id'] ?? null),
                'ip_hash' => $ip ? hash('sha256', $ip) : null, 'user_agent_hash' => $userAgent ? hash('sha256', $userAgent) : null,
            ]);
            $request->statusHistories()->create(['from_status' => null, 'to_status' => 'new']);

            return $request->fresh('preferredVehicleType.translations');
        });
    }

    /** @param list<array<string, mixed>> $items */
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
     * @param  list<Media>  $old
     * @param  list<array<string, mixed>>  $items
     */
    private function syncMedia(Vehicle $vehicle, array $old, array $items): void
    {
        $pivot = [];
        foreach ($items as $item) {
            $id = $this->id(Media::class, $item['media_id']);
            if ($id !== null) {
                $pivot[$id] = Arr::only($item, ['role', 'sort_order']);
            }
        }
        $vehicle->media()->sync($pivot);
        $new = $vehicle->media()->get();
        foreach ($old as $media) {
            if (! $new->contains('id', $media->getKey())) {
                $this->mediaUsage->release($media, 'vehicle', $vehicle->public_id, 'media:'.$media->public_id);
            }
        }
        foreach ($new as $media) {
            $this->mediaUsage->track($media, 'vehicle', $vehicle->public_id, 'media:'.$media->public_id);
        }
    }

    /** @param class-string<Model> $class */
    private function id(string $class, mixed $publicId): ?int
    {
        return is_string($publicId) && $publicId !== '' ? (int) $class::query()->where('public_id', $publicId)->valueOrFail('id') : null;
    }

    private function loadVehicle(Vehicle $vehicle): Vehicle
    {
        return $vehicle->fresh(['translations', 'type.translations', 'media']);
    }

    private function touchCache(): void
    {
        Cache::forever('transportation:version', ((int) Cache::get('transportation:version', 0)) + 1);
    }

    private function record(string $action, User $actor, Model $subject): void
    {
        $this->audit->record(action: $action, actor: $actor, subjectType: $subject->getTable(), subjectPublicId: (string) $subject->getAttribute('public_id'));
    }
}
