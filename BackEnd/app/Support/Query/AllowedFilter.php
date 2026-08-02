<?php

namespace App\Support\Query;

use InvalidArgumentException;

final readonly class AllowedFilter
{
    public function __construct(
        public string $parameter,
        public string $column,
        public FilterValueType $type = FilterValueType::String,
        public FilterOperator $operator = FilterOperator::Equals,
    ) {
        if (! self::isSafeIdentifier($parameter) || ! self::isSafeColumn($column)) {
            throw new InvalidArgumentException('Filter identifiers must be server-defined SQL identifiers.');
        }

        if ($operator === FilterOperator::Contains && $type !== FilterValueType::String) {
            throw new InvalidArgumentException('The contains operator only supports string filters.');
        }
    }

    public function normalize(mixed $value): string|int|bool|null
    {
        return match ($this->type) {
            FilterValueType::String => $this->normalizeString($value),
            FilterValueType::Integer => $this->normalizeInteger($value),
            FilterValueType::Boolean => $this->normalizeBoolean($value),
        };
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeInteger(mixed $value): ?int
    {
        if (! is_int($value) && ! is_string($value)) {
            return null;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($normalized) ? $normalized : null;
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if (! is_bool($value) && ! is_int($value) && ! is_string($value)) {
            return null;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return is_bool($normalized) ? $normalized : null;
    }

    private static function isSafeIdentifier(string $identifier): bool
    {
        return preg_match('/^[a-z][a-z0-9_]*$/', $identifier) === 1;
    }

    private static function isSafeColumn(string $column): bool
    {
        return preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)?$/', $column) === 1;
    }
}
