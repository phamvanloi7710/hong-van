<?php

namespace App\Support\Query;

final readonly class ResolvedSort
{
    public function __construct(
        public string $column,
        public SortDirection $direction,
    ) {}
}
