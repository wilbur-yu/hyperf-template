<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Kernel\Listener;

use App\Kernel\Http\HttpDispatcher as AppHttpDispatcher;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

use function di;

#[Listener]
class HttpDispatcherCheckListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $dispatcher = di(HttpDispatcher::class);

        if (!$dispatcher instanceof AppHttpDispatcher) {
            $logger = di(StdoutLoggerInterface::class);
            $logger->warning(
                sprintf(
                    'HttpDispatcher is not instanceof %s, please set %s => %s in dependencies.',
                    AppHttpDispatcher::class,
                    HttpDispatcher::class,
                    AppHttpDispatcher::class
                )
            );
        }
    }
}
