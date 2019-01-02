<?php
return [
    'server' => [
        'host' => env('REACT_HTTP_HOST', '127.0.0.1'),
        'port' => env('REACT_HTTP_PORT', '8000'),
        'public_path' => base_path('public'),
    ]
];