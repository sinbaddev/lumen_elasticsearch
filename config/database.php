<?php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', 3306),
            'database'  => env('DB_DATABASE', 'lumen'),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
        ],
    ],
    'redis' => [
        'cluster' => false,
        'default' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
        ],
    ],
    
];