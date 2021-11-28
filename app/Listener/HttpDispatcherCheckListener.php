<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Listener;

use App\Kernel\Server\HttpDispatcher as AppHttpDispatcher;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;

#[Listener]
class HttpDispatcherCheckListener implements ListenerInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $dispatcher = $this->container->get(HttpDispatcher::class);

        if (!$dispatcher instanceof AppHttpDispatcher) {
            $logger = $this->container->get(StdoutLoggerInterface::class);
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
