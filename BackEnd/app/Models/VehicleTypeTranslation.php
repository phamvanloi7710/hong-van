<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['vehicle_type_id', 'locale', 'name', 'description'])]
final class VehicleTypeTranslation extends Model
{
    protected $table = 'hongvan_vehicle_type_translations';
}
