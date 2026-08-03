<?php

namespace App\Http\Controllers\Api\V1\CropSolutions;

use App\Domain\CropSolutions\CropSolutionManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CropSolutions\SaveCropCategoryRequest;
use App\Http\Requests\Api\V1\CropSolutions\SaveCropRequest;
use App\Http\Requests\Api\V1\CropSolutions\SaveCropStageRequest;
use App\Http\Resources\Api\V1\CropSolutions\CropCategoryResource;
use App\Http\Resources\Api\V1\CropSolutions\CropResource;
use App\Http\Resources\Api\V1\CropSolutions\CropStageResource;
use App\Http\Responses\ApiResponse;
use App\Models\Crop;
use App\Models\CropCategory;
use App\Models\CropStage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class CropReferenceController extends Controller
{
    public function categories(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', CropCategory::class);
        $categories = CropCategory::query()->with(['translations', 'parent', 'image'])->withCount('crops')->orderBy('sort_order')->get();

        return $response->success(CropCategoryResource::collection($categories)->resolve($request));
    }

    public function storeCategory(SaveCropCategoryRequest $request, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', CropCategory::class);
        $category = $manager->saveCategory($this->actor($request), null, $request->validated());

        return $response->success(CropCategoryResource::make($category)->resolve($request), __('crop_solutions.category_created'), 201);
    }

    public function updateCategory(SaveCropCategoryRequest $request, CropCategory $category, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $category);
        $category = $manager->saveCategory($this->actor($request), $category, $request->validated());

        return $response->success(CropCategoryResource::make($category)->resolve($request), __('crop_solutions.category_updated'));
    }

    public function deleteCategory(Request $request, CropCategory $category, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $category);
        $manager->trashCategory($this->actor($request), $category->load('image'));

        return $response->success(null, __('crop_solutions.category_deleted'));
    }

    public function crops(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Crop::class);
        $crops = Crop::query()->with(['translations', 'category.translations', 'image', 'stages.translations'])->withCount(['stages', 'solutions'])->orderBy('sort_order')->get();

        return $response->success(CropResource::collection($crops)->resolve($request));
    }

    public function storeCrop(SaveCropRequest $request, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Crop::class);
        $crop = $manager->saveCrop($this->actor($request), null, $request->validated());

        return $response->success(CropResource::make($crop)->resolve($request), __('crop_solutions.crop_created'), 201);
    }

    public function updateCrop(SaveCropRequest $request, Crop $crop, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $crop);
        $crop = $manager->saveCrop($this->actor($request), $crop, $request->validated());

        return $response->success(CropResource::make($crop)->resolve($request), __('crop_solutions.crop_updated'));
    }

    public function deleteCrop(Request $request, Crop $crop, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $crop);
        $manager->trashCrop($this->actor($request), $crop->load('image'));

        return $response->success(null, __('crop_solutions.crop_deleted'));
    }

    public function stages(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', CropStage::class);
        $stages = CropStage::query()->with(['translations', 'crop.translations', 'image'])->withCount('solutions')->orderBy('crop_id')->orderBy('sort_order')->get();

        return $response->success(CropStageResource::collection($stages)->resolve($request));
    }

    public function storeStage(SaveCropStageRequest $request, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', CropStage::class);
        $stage = $manager->saveStage($this->actor($request), null, $request->validated());

        return $response->success(CropStageResource::make($stage)->resolve($request), __('crop_solutions.stage_created'), 201);
    }

    public function updateStage(SaveCropStageRequest $request, CropStage $stage, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $stage);
        $stage = $manager->saveStage($this->actor($request), $stage, $request->validated());

        return $response->success(CropStageResource::make($stage)->resolve($request), __('crop_solutions.stage_updated'));
    }

    public function deleteStage(Request $request, CropStage $stage, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $stage);
        $manager->trashStage($this->actor($request), $stage->load('image'));

        return $response->success(null, __('crop_solutions.stage_deleted'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}
