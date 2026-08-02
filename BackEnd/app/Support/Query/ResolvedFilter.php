<?php

namespace App\Support\Query;

final readonly class ResolvedFilter
{
    public function __construct(
        public string $column,
        public string|int|bool $value,
        public FilterOperator $operator,
    ) {}
}
