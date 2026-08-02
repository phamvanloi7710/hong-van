<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Identity\AdminPasswordBroker;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ResetPasswordController extends Controller
{
    public function __invoke(
        ResetPasswordRequest $request,
        AdminPasswordBroker $passwordBroker,
        ApiResponse $response,
    ): JsonResponse {
        $passwordBroker->reset(
            $request,
            $request->string('email')->toString(),
            $request->string('token')->toString(),
            $request->string('password')->toString(),
            $request->string('password_confirmation')->toString(),
        );

        return $response->success(message: __('auth.password_reset'));
    }
}
