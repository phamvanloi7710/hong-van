<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['warehouse_facility_id', 'locale', 'name', 'description'])]
final class WarehouseFacilityTranslation extends Model
{
    protected $table = 'hongvan_warehouse_facility_translations';
}
