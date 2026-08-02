<?php

namespace App\Domain\Identity;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;

final readonly class UserPreferenceService
{
    public function __construct(private IdentityAuditLogger $auditLogger) {}

    /** @return array<string, mixed> */
    public function get(User $user): array
    {
        $namespace = (string) config('admin_preferences.namespace', 'admin');
        $stored = $user->preferences()
            ->where('namespace', $namespace)
            ->get(['key', 'value'])
            ->mapWithKeys(static fn (UserPreference $preference): array => [
                $preference->key => $preference->value,
            ])
            ->all();

        return $this->normalize($stored);
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @return array<string, mixed>
     */
    public function update(User $user, array $preferences): array
    {
        $namespace = (string) config('admin_preferences.namespace', 'admin');
        $keys = array_values(array_intersect(
            ['theme', 'locale', 'favorite_menu_ids'],
            array_keys($preferences),
        ));

        DB::transaction(function () use ($keys, $namespace, $preferences, $user): void {
            foreach ($keys as $key) {
                UserPreference::query()->updateOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'namespace' => $namespace,
                        'key' => $key,
                    ],
                    ['value' => $preferences[$key]],
                );
            }
        });

        if ($keys !== []) {
            $this->auditLogger->record('identity.user_preferences.updated', $user, 'user', $user->public_id, [
                'keys' => implode(',', $keys),
            ]);
        }

        return $this->get($user);
    }

    /** @return array<string, mixed> */
    public function reset(User $user): array
    {
        $user->preferences()
            ->where('namespace', (string) config('admin_preferences.namespace', 'admin'))
            ->delete();

        $this->auditLogger->record('identity.user_preferences.reset', $user, 'user', $user->public_id);

        return $this->defaults();
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private function normalize(array $stored): array
    {
        $defaults = $this->defaults();
        $themeDefaults = is_array($defaults['theme'] ?? null) ? $defaults['theme'] : [];
        $storedTheme = is_array($stored['theme'] ?? null) ? $stored['theme'] : [];
        $theme = [];

        foreach (['fixed_header', 'fixed_sidenav', 'fixed_footer', 'sidenav_opened', 'sidenav_pinned', 'rtl'] as $key) {
            $value = $storedTheme[$key] ?? $themeDefaults[$key] ?? false;
            $theme[$key] = is_bool($value) ? $value : (bool) ($themeDefaults[$key] ?? false);
        }

        $theme['menu_orientation'] = $this->allowedValue(
            $storedTheme['menu_orientation'] ?? null,
            'menu_orientations',
            (string) ($themeDefaults['menu_orientation'] ?? 'vertical'),
        );
        $theme['menu_density'] = $this->allowedValue(
            $storedTheme['menu_density'] ?? null,
            'menu_densities',
            (string) ($themeDefaults['menu_density'] ?? 'default'),
        );
        $theme['skin'] = $this->allowedValue(
            $storedTheme['skin'] ?? null,
            'skins',
            (string) ($themeDefaults['skin'] ?? 'indigo-light'),
        );

        $locale = $this->allowedValue(
            $stored['locale'] ?? null,
            'locales',
            (string) ($defaults['locale'] ?? 'vi'),
        );
        $allowedFavorites = $this->allowed('favorite_menu_ids');
        $storedFavorites = is_array($stored['favorite_menu_ids'] ?? null)
            ? $stored['favorite_menu_ids']
            : [];
        $favoriteMenuIds = array_values(array_unique(array_filter(
            $storedFavorites,
            static fn (mixed $id): bool => is_string($id) && in_array($id, $allowedFavorites, true),
        )));

        return [
            'theme' => $theme,
            'locale' => $locale,
            'favorite_menu_ids' => $favoriteMenuIds,
        ];
    }

    /** @return array<string, mixed> */
    private function defaults(): array
    {
        $templateDefaults = config('admin_preferences.template_defaults', []);
        $systemDefaults = config('admin_preferences.system_defaults', []);

        return array_replace_recursive(
            is_array($templateDefaults) ? $templateDefaults : [],
            is_array($systemDefaults) ? $systemDefaults : [],
        );
    }

    /** @return list<string> */
    private function allowed(string $key): array
    {
        $values = config('admin_preferences.allowed.'.$key, []);

        return array_values(array_filter(
            is_array($values) ? $values : [],
            static fn (mixed $value): bool => is_string($value),
        ));
    }

    private function allowedValue(mixed $value, string $allowlist, string $fallback): string
    {
        return is_string($value) && in_array($value, $this->allowed($allowlist), true)
            ? $value
            : $fallback;
    }
}
