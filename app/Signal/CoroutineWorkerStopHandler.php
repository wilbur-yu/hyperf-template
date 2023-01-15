<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu
 */

namespace App\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\ProcessManager;
use Hyperf\Server\ServerManager;
use Hyperf\Signal\SignalHandlerInterface;

use const SIGINT;
use const SIGTERM;

class CoroutineWorkerStopHandler implements SignalHandlerInterface
{
    #[Inject]
    protected ConfigInterface $config;

    public function listen(): array
    {
        // 协程风格只会存在一个 Worker 进程，故这里只需要监听 WORKER 即可
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // 循环关闭开启的服务
            $server->shutdown();
        }
    }
}
