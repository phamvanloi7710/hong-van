<?php

namespace App\Http\Controllers\Api\V1\CropSolutions;

use App\Domain\CropSolutions\CropSolutionManager;
use App\Domain\CropSolutions\CropSolutionQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CropSolutions\IndexCropSolutionsRequest;
use App\Http\Requests\Api\V1\CropSolutions\SaveCropSolutionRequest;
use App\Http\Resources\Api\V1\CropSolutions\CropSolutionResource;
use App\Http\Responses\ApiResponse;
use App\Models\CropSolution;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class CropSolutionController extends Controller
{
    public function index(IndexCropSolutionsRequest $request, CropSolutionQuery $query, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', CropSolution::class);
        $paginator = $query->paginate($request->validated());

        return $response->paginated(CropSolutionResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(SaveCropSolutionRequest $request, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', CropSolution::class);
        $solution = $manager->createSolution($this->actor($request), $request->validated());

        return $response->success(CropSolutionResource::make($solution)->resolve($request), __('crop_solutions.created'), 201);
    }

    public function show(Request $request, CropSolution $solution, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $solution);
        $solution->load(['translations', 'crop.translations', 'stage.translations', 'heroMedia', 'products.translations']);

        return $response->success(CropSolutionResource::make($solution)->resolve($request));
    }

    public function update(SaveCropSolutionRequest $request, CropSolution $solution, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $solution);
        $solution = $manager->saveSolution($this->actor($request), $solution, $request->validated());

        return $response->success(CropSolutionResource::make($solution)->resolve($request), __('crop_solutions.updated'));
    }

    public function publish(Request $request, CropSolution $solution, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('publish', $solution);

        return $response->success(CropSolutionResource::make($manager->publishSolution($this->actor($request), $solution))->resolve($request), __('crop_solutions.published'));
    }

    public function archive(Request $request, CropSolution $solution, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $solution);

        return $response->success(CropSolutionResource::make($manager->archiveSolution($this->actor($request), $solution))->resolve($request), __('crop_solutions.archived'));
    }

    public function trash(Request $request, CropSolution $solution, CropSolutionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $solution);
        $manager->trashSolution($this->actor($request), $solution->load('heroMedia'));

        return $response->success(null, __('crop_solutions.deleted'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}
