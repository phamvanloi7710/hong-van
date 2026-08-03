<?php

return [
    'cache' => [
        'admin' => 'hongvan.company-settings.admin.v1',
        'public' => 'hongvan.company-settings.public.v1',
    ],
    'groups' => [
        'company' => [
            'label' => 'Company', 'description' => 'Core company identity.',
            'settings' => [
                'company_name' => ['label' => 'Company name', 'type' => 'string', 'public' => true, 'default' => 'CÔNG TY TNHH DV VT HỒNG VÂN', 'rules' => ['nullable', 'string', 'max:255']],
                'short_name' => ['label' => 'Short name', 'type' => 'string', 'public' => true, 'default' => 'HỒNG VÂN', 'rules' => ['nullable', 'string', 'max:120']],
                'default_locale' => ['label' => 'Default locale', 'type' => 'string', 'public' => true, 'default' => 'vi', 'rules' => ['required', 'in:vi,en,zh']],
            ],
        ],
        'legal' => [
            'label' => 'Legal', 'description' => 'Legal company information.',
            'settings' => [
                'legal_name' => ['label' => 'Legal name', 'type' => 'string', 'public' => true, 'default' => 'CÔNG TY TNHH DV VT HỒNG VÂN', 'rules' => ['nullable', 'string', 'max:255']],
                'tax_code' => ['label' => 'Tax code', 'type' => 'string', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:64']],
                'representative' => ['label' => 'Legal representative', 'type' => 'string', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:255']],
            ],
        ],
        'contact' => [
            'label' => 'Contact', 'description' => 'Primary company contact information.',
            'settings' => [
                'primary_phone' => ['label' => 'Primary phone', 'type' => 'string', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:32']],
                'primary_email' => ['label' => 'Primary email', 'type' => 'string', 'public' => true, 'default' => null, 'rules' => ['nullable', 'email', 'max:255']],
                'primary_address' => ['label' => 'Primary address', 'type' => 'text', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:1000']],
            ],
        ],
        'social' => [
            'label' => 'Social', 'description' => 'Social-link behavior.',
            'settings' => [
                'links_nofollow' => ['label' => 'Use nofollow links', 'type' => 'boolean', 'public' => true, 'default' => false, 'rules' => ['required', 'boolean']],
            ],
        ],
        'branding' => [
            'label' => 'Branding', 'description' => 'Brand media references; nullable until P16.',
            'settings' => [
                'logo_media_id' => ['label' => 'Logo media ID', 'type' => 'string', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:26']],
                'favicon_media_id' => ['label' => 'Favicon media ID', 'type' => 'string', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:26']],
                'default_og_media_id' => ['label' => 'Default Open Graph media ID', 'type' => 'string', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:26']],
            ],
        ],
        'business_hours' => [
            'label' => 'Business hours', 'description' => 'Display rules for company business hours.',
            'settings' => [
                'timezone' => ['label' => 'Timezone', 'type' => 'string', 'public' => true, 'default' => 'Asia/Ho_Chi_Minh', 'rules' => ['required', 'timezone:all']],
                'display_note' => ['label' => 'Display note', 'type' => 'text', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:500']],
            ],
        ],
        'map' => [
            'label' => 'Map', 'description' => 'Company map location and embed link.',
            'settings' => [
                'embed_url' => ['label' => 'Map embed URL', 'type' => 'url', 'public' => true, 'default' => null, 'rules' => ['nullable', 'url:http,https', 'max:2000']],
                'latitude' => ['label' => 'Latitude', 'type' => 'decimal', 'public' => true, 'default' => null, 'rules' => ['nullable', 'numeric', 'between:-90,90']],
                'longitude' => ['label' => 'Longitude', 'type' => 'decimal', 'public' => true, 'default' => null, 'rules' => ['nullable', 'numeric', 'between:-180,180']],
            ],
        ],
        'quote' => [
            'label' => 'Quote', 'description' => 'Quote-request behavior.',
            'settings' => [
                'enabled' => ['label' => 'Enable quote requests', 'type' => 'boolean', 'public' => true, 'default' => true, 'rules' => ['required', 'boolean']],
                'recipient_email' => ['label' => 'Quote recipient email', 'type' => 'string', 'public' => false, 'default' => null, 'rules' => ['nullable', 'email', 'max:255']],
                'auto_reply_enabled' => ['label' => 'Enable automatic reply', 'type' => 'boolean', 'public' => false, 'default' => false, 'rules' => ['required', 'boolean']],
                'lead_notifications_enabled' => ['label' => 'Enable lead notifications', 'type' => 'boolean', 'public' => false, 'default' => true, 'rules' => ['required', 'boolean']],
                'database_notifications_enabled' => ['label' => 'Enable database notifications', 'type' => 'boolean', 'public' => false, 'default' => true, 'rules' => ['required', 'boolean']],
            ],
        ],
        'email' => [
            'label' => 'Email', 'description' => 'Email sender and protected delivery settings.',
            'settings' => [
                'from_name' => ['label' => 'Sender name', 'type' => 'string', 'public' => false, 'default' => 'CÔNG TY TNHH DV VT HỒNG VÂN', 'rules' => ['nullable', 'string', 'max:255']],
                'from_address' => ['label' => 'Sender email', 'type' => 'string', 'public' => false, 'default' => null, 'rules' => ['nullable', 'email', 'max:255']],
                'smtp_password' => ['label' => 'SMTP password', 'type' => 'secret', 'public' => false, 'default' => null, 'rules' => ['nullable', 'string', 'max:2000']],
            ],
        ],
        'seo_defaults' => [
            'label' => 'SEO defaults', 'description' => 'Fallback metadata for public pages.',
            'settings' => [
                'site_title' => ['label' => 'Default site title', 'type' => 'string', 'public' => true, 'default' => 'CÔNG TY TNHH DV VT HỒNG VÂN', 'rules' => ['nullable', 'string', 'max:255']],
                'meta_description' => ['label' => 'Default meta description', 'type' => 'text', 'public' => true, 'default' => null, 'rules' => ['nullable', 'string', 'max:500']],
                'public_indexing_enabled' => ['label' => 'Allow public indexing', 'type' => 'boolean', 'public' => true, 'default' => true, 'rules' => ['required', 'boolean']],
            ],
        ],
        'feature_flags' => [
            'label' => 'Feature flags', 'description' => 'Controlled public feature switches.',
            'settings' => [
                'maintenance_banner_enabled' => ['label' => 'Enable maintenance banner', 'type' => 'boolean', 'public' => true, 'default' => false, 'rules' => ['required', 'boolean']],
                'public_contact_forms_enabled' => ['label' => 'Enable public contact forms', 'type' => 'boolean', 'public' => true, 'default' => true, 'rules' => ['required', 'boolean']],
            ],
        ],
    ],
];
