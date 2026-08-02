<?php

namespace App\Http\Middleware;

use App\Support\Http\RequestId;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class AssignRequestId
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/*')) {
            return $next($request);
        }

        $requestId = RequestId::initialize($request);

        Log::shareContext(['request_id' => $requestId]);

        $response = $next($request);
        $response->headers->set(
            (string) config('api.request_id_header', 'X-Request-ID'),
            $requestId,
        );

        return $response;
    }
}
