<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['vehicle_id', 'locale', 'name', 'slug', 'summary', 'description', 'body_dimensions', 'meta_title', 'meta_description'])]
final class VehicleTranslation extends Model
{
    protected $table = 'hongvan_vehicle_translations';
}
