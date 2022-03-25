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
$dependencies = [
    Hyperf\Dispatcher\HttpDispatcher::class => App\Kernel\Http\HttpDispatcher::class,
    Hyperf\HttpServer\Contract\ResponseInterface::class => App\Kernel\Http\Response::class,
    Psr\Http\Message\ResponseInterface::class => App\Kernel\Http\Response::class,
    App\Kernel\Contract\ResponseInterface::class => App\Kernel\Http\Response::class,
    Hyperf\Server\Listener\AfterWorkerStartListener::class => App\Kernel\Listener\WorkerStartListener::class,
    Psr\EventDispatcher\EventDispatcherInterface::class => App\Kernel\Event\EventDispatcherFactory::class,
];
$appEnv = env('APP_ENV', 'dev');
if ($appEnv === 'prod') {
    $dependencies[Hyperf\Contract\StdoutLoggerInterface::class] = App\Kernel\Log\StdoutLoggerFactory::class;
} else {
    $dependencies[Hyperf\Contract\StdoutLoggerInterface::class] = App\Kernel\Log\StdoutLogger::class;
}

return $dependencies;
