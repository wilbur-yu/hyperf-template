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
$dependencies = [
    App\Kernel\Contract\CacheInterface::class                     => App\Kernel\Cache\Cache::class,
    Hyperf\Contract\PackerInterface::class                        => App\Kernel\Utils\Packer\PhpSerializerPacker::class,
    Hyperf\Server\Listener\AfterWorkerStartListener::class        => App\Kernel\Listener\WorkerStartListener::class,
    Hyperf\Crontab\Strategy\StrategyInterface::class              => Hyperf\Crontab\Strategy\CoroutineStrategy::class,
];
$appEnv       = env('APP_ENV', 'dev');
if ($appEnv === 'prod') {
    $dependencies[Hyperf\Contract\StdoutLoggerInterface::class] = App\Kernel\Log\StdoutLoggerFactory::class;
} else {
    $dependencies[Hyperf\Contract\StdoutLoggerInterface::class] = App\Kernel\Log\StdoutLogger::class;
}

return $dependencies;
