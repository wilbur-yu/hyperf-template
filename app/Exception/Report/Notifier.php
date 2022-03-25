<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Exception\Report;

use Guanguans\Notify\Factory;
use Guanguans\Notify\Messages\XiZhiMessage;
use Hyperf\Task\TaskExecutor;
use Hyperf\Task\Task;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Str;
use Throwable;

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

    public function report(array $data, Throwable $throwable, string $title = '业务接口异常报警'): void
    {
        unset($data['exception']);
        $content = self::formatInformation(array_merge($data, $this->simplifyException($throwable)));
        di(TaskExecutor::class)->execute(new Task([self::class, 'xiZhi'], [$content, $title]));
    }

    public static function xiZhi(string $content, string $title): void
    {
        Factory::xiZhi()
            ->setType('single')
            ->setToken('XZad45e6c2bee813d7e9ec46023b161943')
            ->setMessage(
                new XiZhiMessage(
                    $title,
                    transform($content, static function ($content) {
                        return sprintf(self::MARKDOWN_TEMPLATE, $content);
                    })
                )
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

        return [
            'Exception Class' => get_class($exception),
            'Exception Message' => $exception->getMessage(),
            'Exception Code' => $exception->getCode(),
            'Exception File' => $exception->getFile(),
            'Exception Line' => $exception->getLine(),
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
    }
}
