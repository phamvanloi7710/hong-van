<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_status', 'to_status', 'note', 'changed_by'])]
final class WarehouseRequestStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'hongvan_warehouse_request_status_histories';
}
