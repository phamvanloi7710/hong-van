<?php

return [
    'banner' => [
        'title' => 'Your privacy choices',
        'message' => 'We use analytics cookies only with your consent. Necessary cookies remain active so the website can operate securely.',
        'accept_all' => 'Accept all',
        'necessary_only' => 'Necessary only',
        'preferences' => 'Cookie preferences',
        'save' => 'Save choices',
        'revoke' => 'Revoke consent',
        'policy_link' => 'View cookie policy',
    ],
    'categories' => [
        'necessary' => ['label' => 'Necessary', 'description' => 'Required for security, sessions, and core website functions.'],
        'analytics' => ['label' => 'Analytics', 'description' => 'Helps measure views and content performance without sending personally identifiable information.'],
        'marketing' => ['label' => 'Marketing', 'description' => 'Used only for an approved marketing provider when this feature is enabled.'],
    ],
    'messages' => ['saved' => 'Cookie choices saved.', 'revoked' => 'Cookie consent revoked.'],
    'validation' => [
        'provider' => 'The analytics provider is not approved.',
        'identifier_required' => 'A tracking identifier is required when analytics is enabled or the provider changes.',
        'identifier_invalid' => 'The tracking identifier does not match the selected provider.',
    ],
];
