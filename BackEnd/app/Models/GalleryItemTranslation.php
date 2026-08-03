<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['gallery_item_id', 'locale', 'title', 'caption', 'alt_text'])]
final class GalleryItemTranslation extends Model
{
    protected $table = 'hongvan_gallery_item_translations';
}
