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

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * 该工厂类的作用: 将框架本身产生的日志与项目日志统一使用 monolog 来输出.
 */
class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        return Log::get('sys');
    }
}
