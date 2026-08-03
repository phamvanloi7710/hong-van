<?php

namespace App\Models;

use App\Models\Concerns\HasSearchText;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'locale', 'title', 'slug', 'summary', 'content', 'location', 'meta_title', 'meta_description'])]
final class ProjectTranslation extends Model
{
    use HasSearchText;

    protected $table = 'hongvan_project_translations';

    protected function searchTextSourceFields(): array
    {
        return ['title', 'summary'];
    }
}
