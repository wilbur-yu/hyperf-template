<?php

declare(strict_types=1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'driver'    => env('DB_DRIVER', 'mysql'),
        'host'      => env('DB_HOST', 'localhost'),
        'port'      => env('DB_PORT', 3306),
        'database'  => env('DB_DATABASE', 'hyperf'),
        'username'  => env('DB_USERNAME', 'root'),
        'password'  => env('DB_PASSWORD', ''),
        'charset'   => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix'    => env('DB_PREFIX', ''),
        'pool'      => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout'    => 3.0,
            'heartbeat'       => -1,
            'max_idle_time'   => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'commands' => [
            'gen:model' => [
                'path'             => 'app/Model',
                'force_casts'      => true,
                'inheritance'      => 'Model',
                'uses'             => '',
                'table_mapping'    => [],
                'refresh_fillable' => true,
                'with_comments'    => true,
                'visitors'         => [],
            ],
        ],
    ],
];
