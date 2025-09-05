<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*', 'broadcasting/*',],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
    // Local dev
    'http://localhost:3000',
    'http://192.168.1.192:3000',
    'http://localhost:8081',
    'http://localhost:8080',

    // Providers app
    'http://providers.nyla.africa',
    'https://providers.nyla.africa',
    'http://www.providers.nyla.africa',
    'https://www.providers.nyla.africa',

    // Admin app
    'http://admin.nyla.africa',
    'https://admin.nyla.africa',
    'http://www.admin.nyla.africa',
    'https://www.admin.nyla.africa',
],




    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
