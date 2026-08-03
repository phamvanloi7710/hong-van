<?php

return [
    'skip_to_content' => 'Skip to main content',
    'navigation_label' => 'Primary navigation',
    'language_label' => 'Language',
    'navigation' => [
        'home' => 'Home', 'products' => 'Products', 'services' => 'Services', 'transportation' => 'Transportation',
        'warehouses' => 'Warehouses', 'news' => 'News', 'contact' => 'Contact',
        'privacy' => 'Privacy policy', 'terms' => 'Terms of use',
    ],
    'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
    'header' => [
        'utility' => 'Supporting agriculture, transportation and warehousing',
        'brand_caption' => 'Agriculture & logistics',
        'scope' => 'Fertilizer products · Transportation · Warehousing',
        'toggle_menu' => 'Open or close menu',
    ],
    'actions' => [
        'request_quote' => 'Request a quote', 'explore_catalog' => 'Explore catalog', 'learn_more' => 'Learn more',
        'contact_for_catalog' => 'Contact for information', 'request_information' => 'Request consultation',
        'call_now' => 'Call now', 'send_email' => 'Send email', 'view_details' => 'View details',
    ],
    'pages' => [
        'home' => [
            'title' => 'Home', 'eyebrow' => 'Solutions for businesses and growers',
            'heading' => 'Connecting agricultural products with logistics services',
            'intro' => 'Explore Hồng Vân fertilizer products, transportation and warehousing services. Every request is handled through consultation and direct quotation.',
        ],
        'privacy' => ['title' => 'Privacy policy', 'placeholder' => 'The company is currently updating this policy.'],
        'terms' => ['title' => 'Terms of use', 'placeholder' => 'The company is currently updating these terms.'],
    ],
    'home' => [
        'categories' => [
            'label' => 'Content categories', 'title' => 'Main categories', 'products' => 'Fertilizer products',
            'solutions' => 'Crop solutions', 'transportation' => 'Transportation services',
            'warehouses' => 'Warehousing services', 'news' => 'News and insights',
        ],
        'promo' => [
            'transportation_kicker' => 'Business services', 'transportation' => 'Transportation solutions',
            'warehouse_kicker' => 'Operational capabilities', 'warehouse' => 'Warehousing solutions',
        ],
        'benefits' => [
            'label' => 'Key benefits',
            'catalog' => ['title' => 'Clear catalog', 'description' => 'Product information is organized for easy discovery and comparison.'],
            'support' => ['title' => 'Needs-based advice', 'description' => 'Real requirements are reviewed before a suitable solution is proposed.'],
            'quote' => ['title' => 'Direct quotation', 'description' => 'No online sales; every transaction is confirmed through consultation.'],
        ],
        'products' => [
            'eyebrow' => 'Product catalog', 'title' => 'Explore product groups',
            'description' => 'The catalog structure is ready to display published product data from the administration system.',
            'groups' => [
                'nutrition' => ['title' => 'Crop nutrition', 'description' => 'Products supporting nutritional needs at different stages.'],
                'soil' => ['title' => 'Soil care', 'description' => 'Solutions focused on soil and cultivation conditions.'],
                'crop' => ['title' => 'By crop group', 'description' => 'Explore products by the needs of each crop group.'],
                'specialty' => ['title' => 'Specialty products', 'description' => 'Products advised according to specific conditions of use.'],
            ],
            'disclaimer' => 'These are interface navigation groups. Actual products appear only after CMS review and publication.',
        ],
        'services' => [
            'eyebrow' => 'Service capabilities', 'title' => 'One point of contact for multiple needs',
            'description' => 'Service areas are ready to receive verified data from the administration system.',
            'items' => [
                'advisory' => ['title' => 'Solution advisory', 'description' => 'We receive information and guide suitable product groups for each need.'],
                'transportation' => ['title' => 'Transportation', 'description' => 'Capabilities, routes and vehicles appear after their content has been approved.'],
                'warehouses' => ['title' => 'Warehousing', 'description' => 'Locations and warehouse capabilities appear after their content has been approved.'],
            ],
        ],
        'news' => [
            'eyebrow' => 'News and insights', 'title' => 'Updates from Hồng Vân',
            'description' => 'The article area automatically receives content published from the CMS.',
            'pending_title' => 'Content is being updated',
            'pending_description' => 'Articles appear only after editorial review and publication.',
        ],
        'contact' => [
            'eyebrow' => 'Connect with Hồng Vân', 'title' => 'Need advice or a quotation?',
            'description' => 'Contact us directly about products, transportation or warehousing needs.',
            'pending' => 'Contact information is being updated in the administration system.',
        ],
    ],
    'templates' => [
        'empty_title' => 'No content yet', 'empty_description' => 'Content appears after review and publication.',
        'detail_eyebrow' => 'Detailed information', 'quote_title' => 'Need information tailored to your requirements?',
        'quote_description' => 'Send a request for consultation and a direct quotation.',
        'contact_description' => 'Contact information is managed centrally from the administration system.',
        'contact_information' => 'Contact information', 'contact_form' => 'Send a request',
        'contact_form_pending' => 'The form will connect to the existing request intake flow during public integration.',
    ],
    'legal_notice' => 'Please return later for the official content.',
    'legal_identity' => ['name' => 'Legal name', 'tax_code' => 'Tax ID'],
    'breadcrumbs' => 'Breadcrumbs',
    'footer' => [
        'description' => 'A corporate website for fertilizer products, transportation and warehousing services.',
        'explore' => 'Explore', 'policies' => 'Policies', 'contact' => 'Contact',
        'contact_pending' => 'Contact information is being updated.', 'copyright' => 'Copyright belongs to :company.',
    ],
    'errors' => [
        '404' => ['title' => 'Page not found', 'message' => 'The requested page does not exist or has moved.'],
        '500' => ['title' => 'Service temporarily unavailable', 'message' => 'A temporary error occurred. Please try again later.'],
        'back_home' => 'Back to home',
    ],
];
