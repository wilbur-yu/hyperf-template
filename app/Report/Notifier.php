<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu
 */

namespace App\Report;

use Guanguans\Notify\Factory;
use Guanguans\Notify\Messages\DingTalk\MarkdownMessage;
use Guanguans\Notify\Messages\XiZhiMessage;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Str;
use Throwable;

use function array_reduces;
use function di;
use function transform;
use function with;

class Notifier
{
    protected const MARKDOWN_TEMPLATE = <<<'md'
```plain text
%s
```
md;

    public const COLLECTOR = [
        'trigger_time' => true,
        'usage_memory' => true,

        'app_name' => true,
        'app_environment' => true,
        'app_version' => true,
        'app_locale' => true,

        'php_version' => true,
        'php_interface' => true,

        'request_ip_address' => true,
        'request_url' => true,
        'request_method' => true,
        'request_controller_action' => true,
        'request_duration' => true,
        'request_middleware' => false,
        'request_all' => false,
        'request_input' => true,
        'request_header' => false,
        'request_query' => false,
        'request_post' => false,
        'request_server' => false,
        'request_cookie' => false,
        'request_session' => false,

        'exception_stack_trace' => true,
    ];

    public function reportForException(array $data, Throwable $throwable, ?string $title = null): void
    {
        if (environment()->isLocal()) {
            return;
        }
        if ($title === null) {
            if (method_exists($throwable, 'getTitle')) {
                $title = $throwable->getTitle();
            } else {
                $title = '业务接口异常报警';
            }
        }
        $name = '('.config('app_env').') '.config('app_name');
        unset($data['exception']);
        $content = self::formatInformation(array_merge($data, $this->formatException($throwable)));
        $this->send('xiZhi', ["[$name] ".$title, $content]);
        $this->send('dingTalk', ["[$name] ".$title, $content]);
    }

    protected static function formatInformation(array $information): string
    {
        $information = array_filter($information, static function ($info) {
            return filled($info);
        });
        $message = array_reduces($information, static function ($carry, $val, $name) {
            is_scalar($val) or $val = var_export($val, true);

            return $carry.sprintf("%s: %s\n", str_replace('_', ' ', Str::title((string)$name)), $val);
        }, '');

        return trim($message);
    }

    public function formatException(?Throwable $exception = null): array
    {
        if ($exception === null) {
            return [];
        }

        $e = [
            'Exception Class' => get_class($exception),
            'Exception Message' => $exception->getMessage(),
            'Exception Code' => $exception->getCode(),
            'Exception File' => $exception->getFile(),
            'Exception Line' => $exception->getLine(),
            'Exception Carry Data' => method_exists($exception, 'getCustomData') ?
                $exception->getCustomData() : null,
            'Exception Line Preview' => ExceptionContext::getContextAsString($exception),
            'Exception Stack Trace' => with($exception->getTrace(), static function ($trace) {
                if (!self::COLLECTOR['exception_stack_trace']) {
                    return null;
                }

                return collect($trace)
                    ->filter(function ($trace) {
                        return isset($trace['file']) and isset($trace['line']);
                    })
                    ->when(is_callable(self::COLLECTOR['exception_stack_trace']), function (Collection $traces) {
                        return $traces->filter(self::COLLECTOR['exception_stack_trace']);
                    })
                    ->map(function ($trace) {
                        return $trace['file']."({$trace['line']})";
                    })
                    ->values()
                    ->toArray();
            }),
        ];
        $previous = $exception->getPrevious();
        $loop = 1;
        while ($previous !== null) {
            $exceptionClass = get_class($previous);
            $prev["[Previous-$loop] $exceptionClass"] = [
                'Previous Exception Class' => $exceptionClass,
                'Previous Exception Message' => $previous->getMessage(),
                'Previous Exception Code' => $previous->getCode(),
                'Previous Exception File' => $previous->getFile(),
                'Previous Exception Line' => $previous->getLine(),
                'Previous Exception Carry Data' => is_object($previous)
                && method_exists($previous, 'getCustomData') ?
                    $previous->getCustomData() : null,
                'Previous Exception Line Preview' => ExceptionContext::getContextAsString($previous),
                'Previous Exception Stack Trace' => with($previous->getTrace(), static function ($trace) {
                    if (!self::COLLECTOR['exception_stack_trace']) {
                        return null;
                    }

                    return collect($trace)
                        ->filter(function ($trace) {
                            return isset($trace['file']) and isset($trace['line']);
                        })
                        ->when(is_callable(self::COLLECTOR['exception_stack_trace']), function (Collection $traces) {
                            return $traces->filter(self::COLLECTOR['exception_stack_trace']);
                        })
                        ->map(function ($trace) {
                            return $trace['file']."({$trace['line']})";
                        })
                        ->values()
                        ->toArray();
                }),
            ];
            $loop++;
            $previous = $previous->getPrevious();
        }

        return array_merge($e, $prev ?? []);
    }

    // public function report(string $client = 'dingTalk', ...$data): void
    // {
    //     logger('notifier.report')->info('新订单通知');
    //     $this->send($client, ...$data);
    // }

    protected function send(string $client, ...$data): void
    {
        try {
            di(TaskExecutor::class)->execute(new Task([self::class, $client], ...$data));
        } catch (Throwable $e) {
            logger('notifier.send')->error($e->getMessage());
        }
    }

    public function report(array $data, string $title = '业务报告'): void
    {
        if (empty($data)) {
            return;
        }
        $name = '[('.config('app_env').') '.config('app_name').']'.$title;
        $content = self::formatInformation($data);
        // $this->send('xiZhi', ["[$name] ".$title, $content]);
        // $this->send('dingTalk', ["[$name] ".$title, $content]);
        self::xiZhi($name, $content);
        self::dingTalk($name, $content);
    }

    public static function xiZhi(string $title, string $content): void
    {
        logger('notifier.xizhi')->info('[息知]业务消息通知开始');
        $config = config('notify.xiZhi');
        Factory::xiZhi()
            ->setType($config['type'])
            ->setToken($config['token'])
            ->setMessage(
                new XiZhiMessage(
                    $title,
                    transform($content, static function ($content) {
                        return sprintf(self::MARKDOWN_TEMPLATE, $content);
                    })
                )
            )->send();
        logger('notifier.xizhi')->info('[息知]业务消息通知结束');
    }

    public static function dingTalk(string $title, string $content, bool $isAtAll = true): void
    {
        logger('notifier.dingtalk')->info('[钉钉]业务消息通知');
        $config = config('notify.dingTalk');
        Factory::dingTalk()
            ->setToken($config['token'])
            ->setSecret($config['secret'])
            ->setMessage(
                new MarkdownMessage([
                    'title' => $title,
                    'text' => transform($content, static function ($content) {
                        return sprintf(self::MARKDOWN_TEMPLATE, $content);
                    }),
                    'isAtAll' => $isAtAll,
                ])
            )->send();
        logger('notifier.dingtalk')->info('[钉钉]业务消息通知');
    }
}
