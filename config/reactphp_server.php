<?php
return [
    'server' => [
        'host' => env('REACTPHP_HTTP_HOST', '127.0.0.1'),
        'port' => env('REACTPHP_HTTP_PORT', '8000'),
        'public_path' => base_path('public'),
        'options' => [
            'pid_file' => env('REACTPHP_PID_FILE', base_path('storage/logs/reactphp_server.pid')),
            'log_file' => env('REACTPHP_LOG_FILE', base_path('storage/logs/reactphp_server.log')),
            'daemonize' => env('REACTPHP_HTTP_DAEMONIZE', false),
        ]
    ]
];