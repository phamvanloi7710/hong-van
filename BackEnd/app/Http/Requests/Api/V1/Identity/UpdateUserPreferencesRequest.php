<?php

namespace App\Http\Requests\Api\V1\Identity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'theme' => ['sometimes', 'required', 'array:fixed_header,fixed_sidenav,fixed_footer,sidenav_opened,sidenav_pinned,menu_orientation,menu_density,skin,rtl'],
            'theme.fixed_header' => ['required_with:theme', 'boolean'],
            'theme.fixed_sidenav' => ['required_with:theme', 'boolean'],
            'theme.fixed_footer' => ['required_with:theme', 'boolean'],
            'theme.sidenav_opened' => ['required_with:theme', 'boolean'],
            'theme.sidenav_pinned' => ['required_with:theme', 'boolean'],
            'theme.menu_orientation' => ['required_with:theme', 'string', Rule::in(config('admin_preferences.allowed.menu_orientations', []))],
            'theme.menu_density' => ['required_with:theme', 'string', Rule::in(config('admin_preferences.allowed.menu_densities', []))],
            'theme.skin' => ['required_with:theme', 'string', Rule::in(config('admin_preferences.allowed.skins', []))],
            'theme.rtl' => ['required_with:theme', 'boolean'],
            'locale' => ['sometimes', 'required', 'string', Rule::in(config('admin_preferences.allowed.locales', []))],
            'favorite_menu_ids' => ['sometimes', 'array', 'max:'.(int) config('admin_preferences.allowed.max_favorite_menus', 12)],
            'favorite_menu_ids.*' => ['required', 'string', 'distinct', Rule::in(config('admin_preferences.allowed.favorite_menu_ids', []))],
        ];
    }
}
