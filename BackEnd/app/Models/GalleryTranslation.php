<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['gallery_id', 'locale', 'name', 'slug', 'description', 'meta_title', 'meta_description'])]
final class GalleryTranslation extends Model
{
    protected $table = 'hongvan_gallery_translations';
}
