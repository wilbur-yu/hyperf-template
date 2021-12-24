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
use Hyperf\Watcher\Driver\FswatchDriver;

return [
    'driver' => FswatchDriver::class,
    'bin'    => 'php',
    'watch'  => [
        'dir'           => ['app', 'config', 'storage/views'],
        'file'          => ['.env'],
        'scan_interval' => 2000,
    ],
];
