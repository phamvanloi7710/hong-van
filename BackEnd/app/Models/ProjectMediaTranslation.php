<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_media_id', 'locale', 'alt_text', 'caption'])]
final class ProjectMediaTranslation extends Model
{
    protected $table = 'hongvan_project_media_translations';
}
