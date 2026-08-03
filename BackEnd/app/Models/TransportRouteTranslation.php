<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['transport_route_id', 'locale', 'name', 'slug', 'summary'])]
final class TransportRouteTranslation extends Model
{
    protected $table = 'hongvan_transport_route_translations';
}
