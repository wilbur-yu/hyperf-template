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

use App\Middleware\DebugMiddleware;
use App\Middleware\WithRequestInitMiddleware;
use Hyperf\Validation\Middleware\ValidationMiddleware;

return [
    'http' => [
        DebugMiddleware::class,
        ValidationMiddleware::class,
        WithRequestInitMiddleware::class,
    ],
];
