<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['warehouse_service_id', 'locale', 'name', 'description'])]
final class WarehouseServiceTranslation extends Model
{
    protected $table = 'hongvan_warehouse_service_translations';
}
