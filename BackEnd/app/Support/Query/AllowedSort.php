<?php

namespace App\Support\Query;

use InvalidArgumentException;

final readonly class AllowedSort
{
    public function __construct(
        public string $parameter,
        public string $column,
    ) {
        if (preg_match('/^[a-z][a-z0-9_]*$/', $parameter) !== 1
            || preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)?$/', $column) !== 1) {
            throw new InvalidArgumentException('Sort identifiers must be server-defined SQL identifiers.');
        }
    }
}
