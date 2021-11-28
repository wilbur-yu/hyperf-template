<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

use HyperfExt\Encryption\Driver\AesDriver;

return [
    'default' => 'aes',

    'driver' => [
        'aes' => [
            'class' => AesDriver::class,
            'options' => [
                'key' => env('AES_KEY', ''),
                'cipher' => env('AES_CIPHER', 'AES-128-CBC'),
            ],
        ],
    ],
];
