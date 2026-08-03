<?php

namespace App\Models\Concerns;

use App\Domain\Search\SearchTermNormalizer;
use Illuminate\Database\Eloquent\Model;

trait HasSearchText
{
    public static function bootHasSearchText(): void
    {
        static::saving(static function (Model $model): void {
            /** @var self $model */
            $values = array_map(static fn (string $field): string => strip_tags((string) $model->getAttribute($field)), $model->searchTextSourceFields());
            $model->setAttribute('search_text', app(SearchTermNormalizer::class)->fold(implode(' ', $values)));
        });
    }

    /** @return list<string> */
    abstract protected function searchTextSourceFields(): array;
}
