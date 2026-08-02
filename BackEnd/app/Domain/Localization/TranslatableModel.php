<?php

namespace App\Domain\Localization;

use App\Domain\Localization\Concerns\HasTranslations;
use App\Domain\Localization\Contracts\TranslatableEntity;
use Illuminate\Database\Eloquent\Model;

abstract class TranslatableModel extends Model implements TranslatableEntity
{
    use HasTranslations;
}
