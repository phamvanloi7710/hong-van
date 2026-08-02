<?php

return [
    'super_admin' => [
        'email' => env('SUPER_ADMIN_EMAIL'),
        'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
    ],
];
