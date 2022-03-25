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

use WilburYu\HyperfCacheExt\CounterLimiting\Limit;
use WilburYu\HyperfCacheExt\Driver\RedisDriver;
use WilburYu\HyperfCacheExt\Utils\Packer\PhpSerializerPacker;

return [
    'default' => [
        'driver' => RedisDriver::class,
        'packer' => PhpSerializerPacker::class,
        'prefix' => env('APP_NAME', 'skeleton') . ':cache:',
    ],
    'limiter' => [
        'max_attempts' => 20,
        'decay_minutes' => 1,
        'prefix' => 'counter-rate-limit:',
        'for' => [
            'common' => static function () {
                return Limit::perMinute(0);
            },
        ],
        'key' => null,
    ],
];
