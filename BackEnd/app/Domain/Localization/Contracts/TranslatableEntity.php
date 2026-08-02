<?php

namespace App\Domain\Localization\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface TranslatableEntity
{
    /** @return class-string */
    public static function translationModelClass(): string;

    public static function translationForeignKey(): string;

    public static function translationNamespace(): string;

    /** @return HasMany<*, *> */
    public function translations(): HasMany;
}
