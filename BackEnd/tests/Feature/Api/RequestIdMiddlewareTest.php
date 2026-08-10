<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AssignRequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RequestIdMiddlewareTest extends TestCase
{
    public function test_sensitive_request_data_is_not_added_to_log_context(): void
    {
        $requestId = strtolower((string) Str::ulid());
        $request = Request::create(
            '/api/testing/request-id',
            'POST',
            [
                'password' => 'body-secret',
                'credential' => 'credential-secret',
            ],
            ['session' => 'cookie-secret'],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer token-secret',
                'HTTP_X_REQUEST_ID' => $requestId,
            ],
        );

        Log::spy();

        $response = (new AssignRequestId)->handle(
            $request,
            static fn (): Response => response('ok'),
        );

        $normalizedRequestId = strtoupper($requestId);

        $this->assertSame($normalizedRequestId, $request->attributes->get('request_id'));
        $this->assertSame($normalizedRequestId, $response->headers->get('X-Request-ID'));
        Log::shouldHaveReceived('shareContext')
            ->once()
            ->with(['request_id' => $normalizedRequestId]);
    }
}
