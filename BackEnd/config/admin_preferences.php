<?php

return [
    'namespace' => 'admin',

    'template_defaults' => [
        'theme' => [
            'fixed_header' => true,
            'fixed_sidenav' => true,
            'fixed_footer' => false,
            'sidenav_opened' => true,
            'sidenav_pinned' => true,
            'menu_orientation' => 'vertical',
            'menu_density' => 'default',
            'skin' => 'indigo-light',
            'rtl' => false,
        ],
        'locale' => 'vi',
        'favorite_menu_ids' => [],
    ],

    'system_defaults' => [
        'locale' => env('ADMIN_DEFAULT_LOCALE', 'vi'),
    ],

    'allowed' => [
        'locales' => ['vi', 'en', 'zh'],
        'skins' => [
            'indigo-light',
            'teal-light',
            'red-light',
            'gray-light',
            'blue-dark',
            'green-dark',
            'pink-dark',
            'gray-dark',
        ],
        'menu_orientations' => ['vertical', 'horizontal'],
        'menu_densities' => ['default', 'compact', 'mini'],
        'favorite_menu_ids' => ['dashboard', 'identity'],
        'max_favorite_menus' => 12,
    ],
];
