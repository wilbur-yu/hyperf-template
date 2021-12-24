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
        'driver' => App\Kernel\Cache\Driver\RedisDriver::class,
        'packer' => App\Kernel\Utils\Packer\PhpSerializerPacker::class,
        'prefix' => env('APP_NAME', 'skeleton') . ':cache:',
    ],
];
