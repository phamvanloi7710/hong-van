<?php

namespace Tests\Feature\Api;

use App\Exceptions\ConflictException;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Support\Query\AllowedFilter;
use App\Support\Query\AllowedSort;
use App\Support\Query\FilterValueType;
use App\Support\Query\QueryAllowlist;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ApiFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('X-Locale', 'vi');

        Route::middleware('api')->prefix('api/testing/v1')->group(function (): void {
            Route::post('validation', function (ApiFoundationValidationRequest $request, ApiResponse $response) {
                return $response->success($request->validated());
            });

            Route::get('pagination', function (ApiResponse $response) {
                $paginator = new LengthAwarePaginator(
                    items: [['id' => 'item-11'], ['id' => 'item-12']],
                    total: 25,
                    perPage: 10,
                    currentPage: 2,
                );

                return $response->paginated($paginator->items(), $paginator);
            });

            Route::get('query', function (Request $request, ApiResponse $response) {
                $criteria = (new QueryAllowlist(
                    filters: [
                        new AllowedFilter('active', 'hongvan_users.is_active', FilterValueType::Boolean),
                    ],
                    sorts: [
                        new AllowedSort('name', 'hongvan_users.name'),
                    ],
                ))->resolve($request->input('filter'), $request->input('sort'));

                return $response->success([
                    'filters' => array_map(
                        static fn ($filter): array => [
                            'column' => $filter->column,
                            'value' => $filter->value,
                        ],
                        $criteria->filters,
                    ),
                    'sorts' => array_map(
                        static fn ($sort): array => [
                            'column' => $sort->column,
                            'direction' => $sort->direction->value,
                        ],
                        $criteria->sorts,
                    ),
                ]);
            });

            Route::get('conflict', static function (): never {
                throw new ConflictException;
            });

            Route::get('unexpected', static function (): never {
                throw new RuntimeException('internal exception marker');
            });
        });
    }

    public function test_public_v1_ping_uses_success_envelope_and_request_id_log_context(): void
    {
        $requestId = (string) Str::ulid();
        Log::spy();

        $response = $this->withHeader('X-Request-ID', $requestId)
            ->getJson('/api/public/v1/system/ping')
            ->assertOk()
            ->assertHeader('X-Request-ID', $requestId)
            ->assertHeader('Content-Language', 'vi')
            ->assertExactJson([
                'success' => true,
                'data' => ['status' => 'up'],
                'meta' => [
                    'request_id' => $requestId,
                    'pagination' => null,
                ],
                'message' => null,
            ]);

        $this->assertTrue(Str::isUlid((string) $response->headers->get('X-Request-ID')));
        Log::shouldHaveReceived('shareContext')->once()->with(['request_id' => $requestId]);
    }

    public function test_invalid_request_id_is_replaced_and_unsupported_locale_falls_back(): void
    {
        $response = $this->withHeaders([
            'X-Request-ID' => 'not-a-valid-request-id',
            'X-Locale' => 'fr',
            'Accept-Language' => 'fr-FR,fr;q=0.9',
        ])->getJson('/api/public/v1/system/ping')->assertOk();

        $requestId = (string) $response->headers->get('X-Request-ID');

        $this->assertTrue(Str::isUlid($requestId));
        $this->assertNotSame('not-a-valid-request-id', $requestId);
        $response->assertHeader('Content-Language', 'vi')
            ->assertJsonPath('meta.request_id', $requestId);
    }

    public function test_validation_errors_use_the_api_envelope(): void
    {
        $response = $this->postJson('/api/testing/v1/validation', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Dữ liệu không hợp lệ.')
            ->assertJsonStructure([
                'meta' => ['request_id'],
                'errors' => ['name'],
            ]);

        $this->assertTrue(Str::isUlid((string) $response->json('meta.request_id')));
    }

    public function test_not_found_and_production_errors_are_safe(): void
    {
        $notFound = $this->getJson('/api/public/v1/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Không tìm thấy tài nguyên.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');

        $notFound->assertHeader('X-Request-ID', $notFound->json('meta.request_id'));

        config()->set('app.debug', false);

        $this->getJson('/api/testing/v1/unexpected')
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Đã xảy ra lỗi hệ thống.')
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace')
            ->assertDontSee('internal exception marker');
    }

    public function test_authorization_conflict_and_rate_limit_statuses_are_normalized(): void
    {
        $this->getJson('/api/admin/v1/system/ping')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Chưa xác thực.');

        $user = (new User)->forceFill([
            'id' => 1,
            'name' => 'API Test User',
            'email' => 'api-test@example.test',
        ]);

        $this->actingAs($user)
            ->getJson('/api/admin/v1/system/ping')
            ->assertForbidden()
            ->assertJsonPath('message', 'Bạn không có quyền thực hiện thao tác này.');

        $this->getJson('/api/testing/v1/conflict')
            ->assertStatus(409)
            ->assertJsonPath('message', 'Dữ liệu xung đột với trạng thái hiện tại.');

        RateLimiter::for('api', static fn (): Limit => Limit::perMinute(1)->by('api-foundation-test'));

        $this->getJson('/api/public/v1/system/ping')->assertOk();
        $this->getJson('/api/public/v1/system/ping')
            ->assertStatus(429)
            ->assertJsonPath('message', 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.')
            ->assertHeader('Retry-After');
    }

    public function test_pagination_and_typed_query_allowlists_are_stable(): void
    {
        $this->getJson('/api/testing/v1/pagination')
            ->assertOk()
            ->assertJsonPath('meta.pagination', [
                'page' => 2,
                'per_page' => 10,
                'total' => 25,
                'last_page' => 3,
            ]);

        $this->getJson('/api/testing/v1/query?filter[active]=true&sort=-name')
            ->assertOk()
            ->assertJsonPath('data.filters.0', [
                'column' => 'hongvan_users.is_active',
                'value' => true,
            ])
            ->assertJsonPath('data.sorts.0', [
                'column' => 'hongvan_users.name',
                'direction' => 'desc',
            ]);

        $this->getJson('/api/testing/v1/query?sort=hongvan_users.email%20desc')
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['sort']]);
    }

    public function test_api_locale_accepts_allowed_request_locale(): void
    {
        $this->withHeader('X-Locale', 'en')
            ->getJson('/api/public/v1/does-not-exist')
            ->assertNotFound()
            ->assertHeader('Content-Language', 'en')
            ->assertJsonPath('message', 'The requested resource was not found.');
    }
}

final class ApiFoundationValidationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
        ];
    }
}
