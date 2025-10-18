<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // ✅ Allow both frontends (userdashboard & admin)
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:5173'),
    env('FRONTEND_ADMIN_URL', 'http://localhost:5174'),
],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ✅ Keep this true for Sanctum authentication
    'supports_credentials' => true,

];
