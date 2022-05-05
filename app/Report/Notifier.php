<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
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

    protected array $_collector = [
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

    public function exceptionReport(array $data, Throwable $throwable, string $title = '业务接口异常报警'): void
    {
        unset($data['exception']);
        $content = self::formatInformation(array_merge($data, $this->simplifyException($throwable)));
        $this->send('xiZhi', [$title, $content]);
    }

    public static function xiZhi(string $title, string $content): void
    {
        logger('notifier.xizhi')->info('发送异常告警通知');
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
    }

    public function report(string $client = 'dingTalk', ...$data): void
    {
        logger('notifier.report')->info('新订单通知');
        $this->send($client, ...$data);
    }

    protected function send(string $client, ...$data): void
    {
        try {
            di(TaskExecutor::class)->execute(new Task([self::class, $client], ...$data));
        } catch (Throwable $e) {
            logger('notifier.send')->error($e->getMessage());
        }
    }

    public static function dingTalk(string $title, string $text, bool $isAtAll = true): void
    {
        logger('notifier.dingtalk')->info('发送新订单通知');
        $config = config('notify.dingTalk');
        Factory::dingTalk()
            ->setToken($config['token'])
            ->setSecret($config['secret'])
            ->setMessage(
                new MarkdownMessage([
                    'title' => $title,
                    'text' => $text,
                    'isAtAll' => $isAtAll,
                ])
            )->send();
    }

    protected static function formatInformation(array $information): string
    {
        $information = array_filter($information, static function ($info) {
            return filled($info);
        });
        $message = array_reduces($information, static function ($carry, $val, $name) {
            is_scalar($val) or $val = var_export($val, true);

            return $carry.sprintf("%s: %s\n", str_replace('_', ' ', Str::title($name)), $val);
        }, '');

        return trim($message);
    }

    protected function simplifyException(?Throwable $exception = null): array
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
            'Exception Stack Trace' => with($exception->getTrace(), function ($trace) {
                if (!$this->_collector['exception_stack_trace']) {
                    return null;
                }

                return collect($trace)
                    ->filter(function ($trace) {
                        return isset($trace['file']) and isset($trace['line']);
                    })
                    ->when(is_callable($this->_collector['exception_stack_trace']), function (Collection $traces) {
                        return $traces->filter($this->_collector['exception_stack_trace']);
                    })
                    ->map(function ($trace) {
                        return $trace['file']."({$trace['line']})";
                    })
                    ->values()
                    ->toArray();
            }),
        ];
        if ($exception->getPrevious()) {
            $prev = [
                'Previous Exception Class' => get_class($exception->getPrevious()),
                'Previous Exception Message' => $exception->getPrevious()->getMessage(),
                'Previous Exception Code' => $exception->getPrevious()->getCode(),
                'Previous Exception File' => $exception->getPrevious()->getFile(),
                'Previous Exception Line' => $exception->getPrevious()->getLine(),
                'Previous Exception Carry Data' => is_object($exception->getPrevious())
                                                   && method_exists($exception->getPrevious(), 'getCustomData') ?
                    $exception->getPrevious()->getCustomData() : null,
                'Previous Exception Line Preview' => ExceptionContext::getContextAsString($exception),
                'Previous Exception Stack Trace' => with($exception->getTrace(), function ($trace) {
                    if (!$this->_collector['exception_stack_trace']) {
                        return null;
                    }

                    return collect($trace)
                        ->filter(function ($trace) {
                            return isset($trace['file']) and isset($trace['line']);
                        })
                        ->when(is_callable($this->_collector['exception_stack_trace']), function (Collection $traces) {
                            return $traces->filter($this->_collector['exception_stack_trace']);
                        })
                        ->map(function ($trace) {
                            return $trace['file']."({$trace['line']})";
                        })
                        ->values()
                        ->toArray();
                }),
            ];
        }

        return array_merge($e, $prev ?? []);
    }
}
