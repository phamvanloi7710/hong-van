<?php

namespace App\Http\Controllers\Api\V1\Seo;

use App\Domain\Seo\RedirectManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Seo\SaveRedirectRequest;
use App\Http\Responses\ApiResponse;
use App\Models\RedirectRule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RedirectController extends Controller
{
    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        $rules = RedirectRule::query()->orderBy('source_path')->orderBy('locale')->get()->map($this->serialize(...));

        return $response->success($rules);
    }

    public function store(SaveRedirectRequest $request, RedirectManager $manager, ApiResponse $response): JsonResponse
    {
        return $this->save($request, null, $manager, $response, 201);
    }

    public function update(SaveRedirectRequest $request, RedirectRule $redirect, RedirectManager $manager, ApiResponse $response): JsonResponse
    {
        return $this->save($request, $redirect, $manager, $response);
    }

    public function destroy(Request $request, RedirectRule $redirect, RedirectManager $manager, ApiResponse $response): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);
        $manager->delete($redirect, $actor);

        return $response->success(null, __('seo.redirect_deleted'));
    }

    private function save(SaveRedirectRequest $request, ?RedirectRule $redirect, RedirectManager $manager, ApiResponse $response, int $status = 200): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);
        $rule = $manager->save($redirect, $request->validated(), $actor);

        return $response->success($this->serialize($rule), __('seo.redirect_saved'), $status);
    }

    /** @return array<string, mixed> */
    private function serialize(RedirectRule $rule): array
    {
        return [
            'public_id' => $rule->public_id, 'source_path' => $rule->source_path, 'locale' => $rule->locale,
            'target_path' => $rule->target_path, 'status_code' => $rule->status_code, 'is_active' => $rule->is_active,
            'hit_count' => $rule->hit_count,
            'last_hit_at' => $rule->getAttribute('last_hit_at') instanceof \DateTimeInterface ? $rule->getAttribute('last_hit_at')->format(DATE_ATOM) : null,
            'note' => $rule->note,
        ];
    }
}
