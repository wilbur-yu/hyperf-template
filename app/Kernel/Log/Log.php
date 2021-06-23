<?php

declare(strict_types = 1);
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
namespace App\Kernel\Log;

use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class Log
{
    public static function get(string $channel = 'app', string $group = 'default'): LoggerInterface
    {
        return config('app_env') === 'prod'
            ? container()->get(LoggerFactory::class)->get($channel, $group)
            : stdLog();
        // return container()->get(LoggerFactory::class)->get($channel, $group);
    }
}
