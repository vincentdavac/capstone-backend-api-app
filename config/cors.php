<?php

return [

    'paths' => [
        'api/*',
        'admin/*',  // ✅ Add this line
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5174'),
        env('FRONTEND_ADMIN_URL', 'http://localhost:5173'),
        '*',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
