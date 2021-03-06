<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Kernel\Listener;

use App\Kernel\Http\RouteCollector;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;

use function di;

#[Listener]
class InitRouteCollectorListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            MainCoroutineServerStart::class,
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        di(RouteCollector::class);
    }
}
