<?php


return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://yashil-yol.vercel.app',
        'https://ketamiz.com',
        'https://www.ketamiz.com',
        'https://a8b6-91-196-77-111.ngrok-free.app'
    ],

   'allowed_origins_patterns' => [
    '#^https:\/\/.*\.ngrok-free\.app$#',
],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
