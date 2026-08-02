<?php

namespace App\Support\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final readonly class QueryCriteria
{
    /**
     * @param  list<ResolvedFilter>  $filters
     * @param  list<ResolvedSort>  $sorts
     */
    public function __construct(
        public array $filters,
        public array $sorts,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query): Builder
    {
        foreach ($this->filters as $filter) {
            if ($filter->operator === FilterOperator::Contains) {
                $query->where(
                    $filter->column,
                    'like',
                    '%'.addcslashes((string) $filter->value, '\\%_').'%',
                );

                continue;
            }

            $query->where($filter->column, '=', $filter->value);
        }

        foreach ($this->sorts as $sort) {
            $query->orderBy($sort->column, $sort->direction->value);
        }

        return $query;
    }
}
