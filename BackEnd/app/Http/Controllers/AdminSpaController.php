<?php

namespace App\Http\Controllers;

use App\Http\Responses\AdminSpaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AdminSpaController extends Controller
{
    public function __invoke(AdminSpaResponse $response, ?string $path = null): BinaryFileResponse
    {
        return $response->forPath($path);
    }
}
