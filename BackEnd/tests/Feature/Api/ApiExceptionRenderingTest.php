<?php

namespace Tests\Feature\Api;

use App\Exceptions\ConflictException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

class ApiExceptionRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.debug', true);
        $this->withHeader('X-Locale', 'vi');

        Route::middleware('api')->prefix('api/testing/exceptions')->group(function (): void {
            Route::get('401', static fn (): never => throw new AuthenticationException('internal auth marker'));
            Route::get('403', static fn (): never => throw new AuthorizationException('internal authorization marker'));
            Route::get('404', static fn (): never => throw (new ModelNotFoundException)->setModel('Internal\\SecretModel'));
            Route::get('409', static fn (): never => throw new ConflictException);
            Route::get('419', static fn (): never => throw new HttpException(419, 'internal csrf marker'));
            Route::get('422', static fn (): never => throw new HttpException(422, 'internal validation marker'));
            Route::get('429', static fn (): never => throw new TooManyRequestsHttpException(30, 'internal limiter marker'));
            Route::get('500', static fn (): never => throw new RuntimeException('internal exception marker'));
        });
    }

    public function test_api_exceptions_use_safe_localized_status_mappings_without_debug_details(): void
    {
        $cases = [
            401 => 'Chưa xác thực.',
            403 => 'Bạn không có quyền thực hiện thao tác này.',
            404 => 'Không tìm thấy tài nguyên.',
            409 => 'Dữ liệu xung đột với trạng thái hiện tại.',
            419 => 'Phiên làm việc đã hết hạn.',
            422 => 'Dữ liệu không hợp lệ.',
            429 => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.',
            500 => 'Đã xảy ra lỗi hệ thống.',
        ];

        foreach ($cases as $status => $message) {
            $response = $this->getJson("/api/testing/exceptions/{$status}")
                ->assertStatus($status)
                ->assertJsonPath('success', false)
                ->assertJsonPath('data', null)
                ->assertJsonPath('message', $message)
                ->assertJsonStructure(['meta' => ['request_id']])
                ->assertJsonMissingPath('exception')
                ->assertJsonMissingPath('file')
                ->assertJsonMissingPath('line')
                ->assertJsonMissingPath('trace');

            $response->assertHeader('X-Request-ID', $response->json('meta.request_id'));
            $this->assertStringNotContainsString('internal', $response->getContent());
        }
    }
}
