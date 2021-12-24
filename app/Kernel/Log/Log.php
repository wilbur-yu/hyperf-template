<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Kernel\Log;

use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class Log
{
    public static function get(string $channel = 'app', string $group = 'default'): LoggerInterface
    {
        return 'dev' === config('app_env')
            ? stdLog()
            : di(LoggerFactory::class)->get($channel, $group);
    }
}
