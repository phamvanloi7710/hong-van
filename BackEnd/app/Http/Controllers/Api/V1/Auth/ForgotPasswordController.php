<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Identity\AdminPasswordBroker;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ForgotPasswordController extends Controller
{
    public function __invoke(
        ForgotPasswordRequest $request,
        AdminPasswordBroker $passwordBroker,
        ApiResponse $response,
    ): JsonResponse {
        $passwordBroker->sendResetLink($request->string('email')->toString());

        return $response->success(message: __('auth.password_link_sent'));
    }
}
