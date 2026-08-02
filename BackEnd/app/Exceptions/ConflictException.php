<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

final class ConflictException extends RuntimeException implements ShouldntReport
{
    // Domain actions may provide a safe user-facing message when needed.
}
