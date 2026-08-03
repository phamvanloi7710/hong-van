<?php

return [
    'skip_to_content' => 'Skip to main content',
    'navigation_label' => 'Primary navigation',
    'language_label' => 'Language',
    'navigation' => ['home' => 'Home', 'privacy' => 'Privacy policy', 'terms' => 'Terms of use'],
    'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
    'pages' => [
        'home' => [
            'title' => 'Home',
            'eyebrow' => 'Corporate website platform',
            'heading' => 'The public foundation is ready',
            'intro' => 'Content is being prepared for the official Laravel Blade interface integration.',
            'status_title' => 'Server-rendered content',
            'status_body' => 'Core text remains readable when JavaScript is unavailable.',
        ],
        'privacy' => ['title' => 'Privacy policy', 'placeholder' => 'The company is currently updating this policy.'],
        'terms' => ['title' => 'Terms of use', 'placeholder' => 'The company is currently updating these terms.'],
    ],
    'legal_notice' => 'Please return later for the official content.',
    'legal_identity' => ['name' => 'Legal name', 'tax_code' => 'Tax ID'],
    'breadcrumbs' => 'Breadcrumbs',
    'footer' => ['copyright' => 'Copyright belongs to :company.'],
    'errors' => [
        '404' => ['title' => 'Page not found', 'message' => 'The requested page does not exist or has moved.'],
        '500' => ['title' => 'Service temporarily unavailable', 'message' => 'A temporary error occurred. Please try again later.'],
        'back_home' => 'Back to home',
    ],
];
