<?php

return [
    'cache_key' => 'hongvan.public-theme.published.v1',
    'fonts' => [
        'system_sans' => '"Segoe UI", Roboto, Helvetica, Arial, sans-serif',
        'serif' => 'Georgia, "Times New Roman", serif',
    ],
    'shadow_presets' => [
        'none' => 'none',
        'soft' => '0 12px 32px rgb(26 76 44 / 10%)',
        'raised' => '0 18px 46px rgb(26 76 44 / 18%)',
    ],
    'animation_presets' => [
        'none' => ['duration' => '0ms', 'easing' => 'linear'],
        'subtle' => ['duration' => '160ms', 'easing' => 'ease-out'],
        'standard' => ['duration' => '240ms', 'easing' => 'cubic-bezier(.2,.8,.2,1)'],
    ],
    'defaults' => [
        'colors' => [
            'brand' => '#63b82e', 'brand_strong' => '#27863a', 'brand_deep' => '#17692e',
            'brand_soft' => '#edf8e8', 'accent' => '#f2a622', 'surface' => '#ffffff',
            'surface_muted' => '#f5f5f3', 'surface_dark' => '#183b27', 'text' => '#303631',
            'text_muted' => '#687069', 'border' => '#e2e6e1', 'focus' => '#075fd8',
        ],
        'fonts' => ['body' => 'system_sans', 'heading' => 'system_sans'],
        'sizes' => ['base' => 16, 'small' => 14, 'large' => 20, 'h1_min' => 32, 'h1_max' => 55, 'h2_min' => 28, 'h2_max' => 39],
        'spacing' => ['xs' => 4, 'sm' => 8, 'md' => 12, 'lg' => 16, 'xl' => 24, '2xl' => 32, '3xl' => 48],
        'radii' => ['small' => 4, 'medium' => 10, 'large' => 16, 'pill' => 999],
        'shadows' => ['preset' => 'soft'],
        'containers' => ['max' => 1200, 'narrow' => 832, 'gutter_min' => 16, 'gutter_max' => 32],
        'buttons' => ['min_height' => 44, 'horizontal_padding' => 24, 'radius' => 'small', 'font_weight' => 700],
        'headings' => ['font_weight' => 800, 'line_height' => 1.15],
        'sections' => ['gap' => 72],
        'animation' => ['preset' => 'standard'],
    ],
];
