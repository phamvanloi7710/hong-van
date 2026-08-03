<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['certification_id', 'locale', 'name', 'slug', 'issuer', 'description', 'image_alt', 'document_label'])]
final class CertificationTranslation extends Model
{
    protected $table = 'hongvan_certification_translations';
}
