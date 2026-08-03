<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['warehouse_id', 'locale', 'name', 'slug', 'summary', 'description', 'address_display', 'area_description', 'capacity_description', 'security_description', 'fire_safety_description', 'business_hours_description', 'meta_title', 'meta_description'])]
final class WarehouseTranslation extends Model
{
    protected $table = 'hongvan_warehouse_translations';
}
