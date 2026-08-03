<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['transport_request_id', 'from_status', 'to_status', 'note', 'changed_by'])]
final class TransportRequestStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'hongvan_transport_request_status_histories';
}
