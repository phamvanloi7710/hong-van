<?php

namespace App\Http\Exceptions;

use App\Exceptions\ConflictException;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

final readonly class ApiExceptionRenderer
{
    public function __construct(private ApiResponse $response) {}

    public function render(Throwable $exception, Request $request): JsonResponse
    {
        return match (true) {
            $exception instanceof ValidationException => $this->response->error(
                __('api.validation'),
                $exception->status,
                $exception->errors(),
            ),
            $exception instanceof AuthenticationException => $this->response->error(
                __('api.unauthenticated'),
                401,
            ),
            $exception instanceof AuthorizationException,
            $exception instanceof AccessDeniedHttpException => $this->response->error(
                __('api.forbidden'),
                403,
            ),
            $exception instanceof ModelNotFoundException,
            $exception instanceof NotFoundHttpException => $this->response->error(
                __('api.not_found'),
                404,
            ),
            $exception instanceof ConflictException => $this->response->error(
                $exception->getMessage() !== '' ? $exception->getMessage() : __('api.conflict'),
                409,
            ),
            $exception instanceof TooManyRequestsHttpException => $this->response->error(
                __('api.rate_limited'),
                429,
                headers: $exception->getHeaders(),
            ),
            $exception instanceof HttpExceptionInterface => $this->httpException($exception),
            default => $this->response->error(__('api.server_error'), 500),
        };
    }

    private function httpException(HttpExceptionInterface $exception): JsonResponse
    {
        $status = $exception->getStatusCode();
        $message = match ($status) {
            401 => __('api.unauthenticated'),
            403 => __('api.forbidden'),
            404 => __('api.not_found'),
            409 => __('api.conflict'),
            419 => __('api.session_expired'),
            429 => __('api.rate_limited'),
            default => $status >= 500 ? __('api.server_error') : __('api.bad_request'),
        };

        return $this->response->error(
            $message,
            $status,
            headers: $exception->getHeaders(),
        );
    }
}
