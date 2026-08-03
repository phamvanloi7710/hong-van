<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['transport_service_area_id', 'locale', 'name', 'slug', 'summary'])]
final class TransportServiceAreaTranslation extends Model
{
    protected $table = 'hongvan_transport_service_area_translations';
}
