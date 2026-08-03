<?php

namespace App\Domain\CropSolutions;

use App\Domain\Audit\AuditTrail;
use App\Domain\Localization\TranslatableModel;
use App\Domain\Media\MediaUsageTracker;
use App\Exceptions\ConflictException;
use App\Models\Crop;
use App\Models\CropCategory;
use App\Models\CropSolution;
use App\Models\CropStage;
use App\Models\Media;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CropSolutionManager
{
    public function __construct(
        private MediaUsageTracker $mediaUsage,
        private AuditTrail $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function saveCategory(User $actor, ?CropCategory $category, array $data): CropCategory
    {
        $category = DB::transaction(function () use ($actor, $category, $data): CropCategory {
            $category ??= new CropCategory;
            $oldImage = $category->image;
            $category->fill([
                ...Arr::only($data, ['code', 'is_active', 'sort_order']),
                'parent_id' => $this->internalId(CropCategory::class, $data['parent_id'] ?? null),
                'image_media_id' => $this->internalId(Media::class, $data['image_media_id'] ?? null),
                $category->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($category, $data['translations']);
            $this->syncMediaUsage($oldImage, $category->fresh()->image, 'crop_category', $category->public_id, 'image');
            $this->record($category->wasRecentlyCreated ? 'crop_category.created' : 'crop_category.updated', $actor, $category);

            return $this->loadCategory($category);
        });
        $this->touchCache();

        return $category;
    }

    public function trashCategory(User $actor, CropCategory $category): void
    {
        if ($category->children()->exists() || $category->crops()->exists()) {
            throw new ConflictException(__('crop_solutions.category_in_use'));
        }
        $this->releaseMedia($category->image, 'crop_category', $category->public_id, 'image');
        $category->forceFill(['deleted_by' => $actor->getKey()])->save();
        $category->delete();
        $this->record('crop_category.trashed', $actor, $category);
        $this->touchCache();
    }

    /** @param array<string, mixed> $data */
    public function saveCrop(User $actor, ?Crop $crop, array $data): Crop
    {
        $crop = DB::transaction(function () use ($actor, $crop, $data): Crop {
            $crop ??= new Crop;
            $oldImage = $crop->image;
            $crop->fill([
                ...Arr::only($data, ['code', 'is_active', 'sort_order']),
                'crop_category_id' => $this->internalId(CropCategory::class, $data['category_id'] ?? null),
                'image_media_id' => $this->internalId(Media::class, $data['image_media_id'] ?? null),
                $crop->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($crop, $data['translations']);
            $this->syncMediaUsage($oldImage, $crop->fresh()->image, 'crop', $crop->public_id, 'image');
            $this->record($crop->wasRecentlyCreated ? 'crop.created' : 'crop.updated', $actor, $crop);

            return $this->loadCrop($crop);
        });
        $this->touchCache();

        return $crop;
    }

    public function trashCrop(User $actor, Crop $crop): void
    {
        if ($crop->stages()->exists() || $crop->solutions()->exists()) {
            throw new ConflictException(__('crop_solutions.crop_in_use'));
        }
        $this->releaseMedia($crop->image, 'crop', $crop->public_id, 'image');
        $crop->forceFill(['deleted_by' => $actor->getKey()])->save();
        $crop->delete();
        $this->record('crop.trashed', $actor, $crop);
        $this->touchCache();
    }

    /** @param array<string, mixed> $data */
    public function saveStage(User $actor, ?CropStage $stage, array $data): CropStage
    {
        $stage = DB::transaction(function () use ($actor, $stage, $data): CropStage {
            $stage ??= new CropStage;
            $oldImage = $stage->image;
            $stage->fill([
                ...Arr::only($data, ['code', 'is_active', 'sort_order']),
                'crop_id' => $this->internalId(Crop::class, $data['crop_id']),
                'image_media_id' => $this->internalId(Media::class, $data['image_media_id'] ?? null),
                $stage->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($stage, $data['translations']);
            $this->syncMediaUsage($oldImage, $stage->fresh()->image, 'crop_stage', $stage->public_id, 'image');
            $this->record($stage->wasRecentlyCreated ? 'crop_stage.created' : 'crop_stage.updated', $actor, $stage);

            return $this->loadStage($stage);
        });
        $this->touchCache();

        return $stage;
    }

    public function trashStage(User $actor, CropStage $stage): void
    {
        if ($stage->solutions()->exists()) {
            throw new ConflictException(__('crop_solutions.stage_in_use'));
        }
        $this->releaseMedia($stage->image, 'crop_stage', $stage->public_id, 'image');
        $stage->forceFill(['deleted_by' => $actor->getKey()])->save();
        $stage->delete();
        $this->record('crop_stage.trashed', $actor, $stage);
        $this->touchCache();
    }

    /** @param array<string, mixed> $data */
    public function createSolution(User $actor, array $data): CropSolution
    {
        return $this->saveSolution($actor, new CropSolution, $data);
    }

    /** @param array<string, mixed> $data */
    public function saveSolution(User $actor, CropSolution $solution, array $data): CropSolution
    {
        $solution = DB::transaction(function () use ($actor, $solution, $data): CropSolution {
            $cropId = $this->internalId(Crop::class, $data['crop_id']);
            $stageId = $this->internalId(CropStage::class, $data['stage_id'] ?? null);
            if ($stageId !== null && ! CropStage::query()->whereKey($stageId)->where('crop_id', $cropId)->exists()) {
                throw ValidationException::withMessages(['stage_id' => [__('crop_solutions.stage_crop_mismatch')]]);
            }
            $oldHero = $solution->heroMedia;
            $solution->fill([
                ...Arr::only($data, ['code', 'status', 'is_featured', 'sort_order', 'published_at', 'unpublished_at']),
                'crop_id' => $cropId,
                'crop_stage_id' => $stageId,
                'hero_media_id' => $this->internalId(Media::class, $data['hero_media_id'] ?? null),
                $solution->exists ? 'updated_by' : 'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->save();
            $this->syncTranslations($solution, $data['translations']);
            $this->syncRecommendedProducts($solution, $data['products'] ?? []);
            $this->syncMediaUsage($oldHero, $solution->fresh()->heroMedia, 'crop_solution', $solution->public_id, 'hero');
            $this->record($solution->wasRecentlyCreated ? 'crop_solution.created' : 'crop_solution.updated', $actor, $solution);

            return $this->loadSolution($solution);
        });
        $this->touchCache();

        return $solution;
    }

    public function publishSolution(User $actor, CropSolution $solution): CropSolution
    {
        $solution->forceFill([
            'status' => 'published',
            'published_at' => $solution->published_at ?? now('UTC'),
            'unpublished_at' => null,
            'updated_by' => $actor->getKey(),
        ])->save();
        $this->record('crop_solution.published', $actor, $solution);
        $this->touchCache();

        return $this->loadSolution($solution);
    }

    public function archiveSolution(User $actor, CropSolution $solution): CropSolution
    {
        $solution->forceFill(['status' => 'archived', 'updated_by' => $actor->getKey()])->save();
        $this->record('crop_solution.archived', $actor, $solution);
        $this->touchCache();

        return $this->loadSolution($solution);
    }

    public function trashSolution(User $actor, CropSolution $solution): CropSolution
    {
        $this->releaseMedia($solution->heroMedia, 'crop_solution', $solution->public_id, 'hero');
        $solution->forceFill(['deleted_by' => $actor->getKey()])->save();
        $solution->delete();
        $this->record('crop_solution.trashed', $actor, $solution);
        $this->touchCache();

        return $solution;
    }

    /** @param list<array<string, mixed>> $translations */
    private function syncTranslations(TranslatableModel $model, array $translations): void
    {
        $locales = [];
        foreach ($translations as $translation) {
            $locale = $translation['locale'];
            $locales[] = $locale;
            $model->translations()->updateOrCreate(['locale' => $locale], Arr::except($translation, ['locale']));
        }
        $model->translations()->whereNotIn('locale', $locales)->delete();
    }

    /** @param list<array<string, mixed>> $products */
    private function syncRecommendedProducts(CropSolution $solution, array $products): void
    {
        $pivot = [];
        foreach ($products as $product) {
            $productId = $this->internalId(Product::class, $product['product_id']);
            if ($productId !== null) {
                $pivot[$productId] = Arr::only($product, ['sort_order', 'recommendation_note']);
            }
        }
        $solution->products()->sync($pivot);
    }

    private function syncMediaUsage(?Media $old, ?Media $new, string $ownerType, string $ownerPublicId, string $field): void
    {
        if ($old instanceof Media && $old->getKey() !== $new?->getKey()) {
            $this->mediaUsage->release($old, $ownerType, $ownerPublicId, $field);
        }
        if ($new instanceof Media) {
            $this->mediaUsage->track($new, $ownerType, $ownerPublicId, $field);
        }
    }

    private function releaseMedia(?Media $media, string $ownerType, string $ownerPublicId, string $field): void
    {
        if ($media instanceof Media) {
            $this->mediaUsage->release($media, $ownerType, $ownerPublicId, $field);
        }
    }

    /** @param class-string<Model> $modelClass */
    private function internalId(string $modelClass, mixed $publicId): ?int
    {
        if (! is_string($publicId) || $publicId === '') {
            return null;
        }

        return (int) $modelClass::query()->where('public_id', $publicId)->valueOrFail('id');
    }

    private function loadCategory(CropCategory $category): CropCategory
    {
        return $category->fresh(['translations', 'parent.translations', 'image']);
    }

    private function loadCrop(Crop $crop): Crop
    {
        return $crop->fresh(['translations', 'category.translations', 'image', 'stages.translations']);
    }

    private function loadStage(CropStage $stage): CropStage
    {
        return $stage->fresh(['translations', 'crop.translations', 'image']);
    }

    private function loadSolution(CropSolution $solution): CropSolution
    {
        return $solution->fresh(['translations', 'crop.translations', 'stage.translations', 'heroMedia', 'products.translations']);
    }

    private function touchCache(): void
    {
        Cache::forever('crop_solutions:version', ((int) Cache::get('crop_solutions:version', 0)) + 1);
    }

    /** @param array<string, mixed> $details */
    private function record(string $action, User $actor, Model $subject, array $details = []): void
    {
        $this->audit->record(
            action: $action,
            actor: $actor,
            subjectType: $subject->getTable(),
            subjectPublicId: (string) $subject->getAttribute('public_id'),
            after: $details,
        );
    }
}
