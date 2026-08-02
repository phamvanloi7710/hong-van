<?php

namespace App\Support\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class RequestId
{
    public const ATTRIBUTE = 'request_id';

    public static function initialize(Request $request): string
    {
        $header = (string) config('api.request_id_header', 'X-Request-ID');
        $incoming = trim((string) $request->header($header, ''));
        $requestId = Str::isUlid($incoming)
            ? strtoupper($incoming)
            : (string) Str::ulid();

        $request->attributes->set(self::ATTRIBUTE, $requestId);

        return $requestId;
    }

    public static function getOrCreate(Request $request): string
    {
        $requestId = $request->attributes->get(self::ATTRIBUTE);

        return is_string($requestId) && Str::isUlid($requestId)
            ? $requestId
            : self::initialize($request);
    }
}
