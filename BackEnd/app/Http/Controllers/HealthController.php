<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Throwable;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            Event::dispatch(new DiagnosingHealth);
        } catch (Throwable) {
            logger()->warning('Application health diagnostic failed.');

            return response()->json(['status' => 'down'], 500);
        }

        return response()->json(['status' => 'up']);
    }
}
