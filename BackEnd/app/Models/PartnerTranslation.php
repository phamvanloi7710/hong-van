<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['partner_id', 'locale', 'name', 'description', 'logo_alt'])]
final class PartnerTranslation extends Model
{
    protected $table = 'hongvan_partner_translations';
}
