<?php

namespace App\Support\Query;

use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class QueryAllowlist
{
    /** @var array<string, AllowedFilter> */
    private array $filters = [];

    /** @var array<string, AllowedSort> */
    private array $sorts = [];

    /**
     * @param  list<AllowedFilter>  $filters
     * @param  list<AllowedSort>  $sorts
     */
    public function __construct(array $filters = [], array $sorts = [])
    {
        foreach ($filters as $filter) {
            if (isset($this->filters[$filter->parameter])) {
                throw new InvalidArgumentException("Duplicate filter parameter: {$filter->parameter}");
            }

            $this->filters[$filter->parameter] = $filter;
        }

        foreach ($sorts as $sort) {
            if (isset($this->sorts[$sort->parameter])) {
                throw new InvalidArgumentException("Duplicate sort parameter: {$sort->parameter}");
            }

            $this->sorts[$sort->parameter] = $sort;
        }
    }

    public function resolve(mixed $filters, mixed $sort): QueryCriteria
    {
        return new QueryCriteria(
            filters: $this->resolveFilters($filters),
            sorts: $this->resolveSorts($sort),
        );
    }

    /**
     * @return list<ResolvedFilter>
     */
    private function resolveFilters(mixed $input): array
    {
        if ($input === null || $input === []) {
            return [];
        }

        if (! is_array($input)) {
            throw ValidationException::withMessages([
                'filter' => [__('api.invalid_filter', ['filter' => 'filter'])],
            ]);
        }

        $resolved = [];

        foreach ($input as $parameter => $value) {
            if (! is_string($parameter) || ! isset($this->filters[$parameter])) {
                $name = is_string($parameter) ? $parameter : 'filter';

                throw ValidationException::withMessages([
                    "filter.{$name}" => [__('api.invalid_filter', ['filter' => $name])],
                ]);
            }

            $definition = $this->filters[$parameter];
            $normalized = $definition->normalize($value);

            if ($normalized === null) {
                throw ValidationException::withMessages([
                    "filter.{$parameter}" => [__('api.invalid_filter_value', ['filter' => $parameter])],
                ]);
            }

            $resolved[] = new ResolvedFilter(
                column: $definition->column,
                value: $normalized,
                operator: $definition->operator,
            );
        }

        return $resolved;
    }

    /**
     * @return list<ResolvedSort>
     */
    private function resolveSorts(mixed $input): array
    {
        if ($input === null || $input === '') {
            return [];
        }

        if (! is_string($input)) {
            throw ValidationException::withMessages([
                'sort' => [__('api.invalid_sort', ['sort' => 'sort'])],
            ]);
        }

        $resolved = [];

        foreach (explode(',', $input) as $sortValue) {
            $sortValue = trim($sortValue);
            $descending = str_starts_with($sortValue, '-');
            $parameter = $descending ? substr($sortValue, 1) : $sortValue;

            if ($parameter === '' || ! isset($this->sorts[$parameter])) {
                throw ValidationException::withMessages([
                    'sort' => [__('api.invalid_sort', ['sort' => $sortValue])],
                ]);
            }

            $resolved[] = new ResolvedSort(
                column: $this->sorts[$parameter]->column,
                direction: $descending ? SortDirection::Descending : SortDirection::Ascending,
            );
        }

        return $resolved;
    }
}
