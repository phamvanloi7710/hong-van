<?php

namespace App\Http\Responses;

use App\Support\Http\PaginationMeta;
use App\Support\Http\RequestId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ApiResponse
{
    public function __construct(private Request $request) {}

    /**
     * @param  array<string, string>  $headers
     */
    public function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        array $headers = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'request_id' => RequestId::getOrCreate($this->request),
                'pagination' => null,
            ],
            'message' => $message,
        ], $status, $this->withRequestIdHeader($headers), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  LengthAwarePaginator<array-key, mixed>  $paginator
     * @param  array<string, string>  $headers
     */
    public function paginated(
        mixed $data,
        LengthAwarePaginator $paginator,
        ?string $message = null,
        array $headers = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'request_id' => RequestId::getOrCreate($this->request),
                'pagination' => PaginationMeta::fromPaginator($paginator)->toArray(),
            ],
            'message' => $message,
        ], 200, $this->withRequestIdHeader($headers), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  array<string, list<string>>  $errors
     * @param  array<string, string>  $headers
     */
    public function error(
        string $message,
        int $status,
        array $errors = [],
        array $headers = [],
    ): JsonResponse {
        $payload = [
            'success' => false,
            'data' => null,
            'meta' => [
                'request_id' => RequestId::getOrCreate($this->request),
            ],
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json(
            $payload,
            $status,
            $this->withRequestIdHeader($headers),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    private function withRequestIdHeader(array $headers): array
    {
        $headers[(string) config('api.request_id_header', 'X-Request-ID')] = RequestId::getOrCreate($this->request);

        return $headers;
    }
}
