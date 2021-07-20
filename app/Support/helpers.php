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
use App\Kernel\Contract\CacheInterface;
use App\Kernel\Contract\ResponseInterface;
use App\Kernel\Log\Log;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Server\ServerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server;
use Swoole\WebSocket\Frame;

if (! function_exists('format_duration')) {
    /**
     * Format duration.
     *
     * @param float $seconds
     *
     * @return string
     */
    function format_duration(float $seconds): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        }

        if ($seconds < 1) {
            return round($seconds * 1000, 2) . 'ms';
        }

        return round($seconds, 2) . 's';
    }
}

if (! function_exists('hide_str')) {
    /**
     * 将一个字符串部分字符用$re替代隐藏.
     *
     * @param null|string $string 待处理的字符串
     * @param int         $start  规定在字符串的何处开始，
     *                            正数 - 在字符串的指定位置开始
     *                            负数 - 在从字符串结尾的指定位置开始
     *                            0 - 在字符串中的第一个字符处开始
     * @param int         $length 可选。规定要隐藏的字符串长度。默认是直到字符串的结尾。
     *                            正数 - 从 start 参数所在的位置隐藏
     *                            负数 - 从字符串末端隐藏
     * @param string      $re     替代符
     *
     * @return bool|string 处理后的字符串
     */
    function hide_str(?string $string, int $start = 0, int $length = 0, string $re = '*'): bool | string
    {
        if (empty($string)) {
            return '';
        }
        $strArr   = [];
        $mbStrLen = mb_strlen($string);
        //循环把字符串变为数组
        while ($mbStrLen) {
            $strArr[] = mb_substr($string, 0, 1, 'utf8');
            $string   = mb_substr($string, 1, $mbStrLen, 'utf8');
            $mbStrLen = mb_strlen($string);
        }
        $strLen = count($strArr);
        $begin  = $start >= 0 ? $start : ($strLen - abs($start));
        $end    = $last = $strLen - 1;
        if ($length > 0) {
            $end = $begin + $length - 1;
        } elseif ($length < 0) {
            $end -= abs($length);
        }
        for ($i = $begin; $i <= $end; ++$i) {
            $strArr[$i] = $re;
        }
        if ($begin >= $end || $begin >= $last || $end > $last) {
            return false;
        }

        return implode('', $strArr);
    }
}

if (! function_exists('app')) {
    function app(): ContainerInterface
    {
        return container();
    }
}

if (! function_exists('di')) {
    function di(): ContainerInterface
    {
        return container();
    }
}

if (! function_exists('get_available_no')) {
    /**
     * 支持中小型支付系统，单机房生成订单号QPS<=1w，保证订单号绝对唯一
     */
    function get_available_no(string $prefix = ''): string
    {
        return $prefix . date('YmdHis') .
               substr(implode(null, array_map('ord', str_split(substr(uniqid('', true), 7, 13)))), 0, 8);
    }
}

if (! function_exists('container')) {
    function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}

if (! function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     *
     * @param \Throwable $throwable
     *
     * @return string
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('throw_if')) {
    /**
     * @license https://github.com/laravel/framework
     * Throw the given exception if the given condition is true.
     *
     * @param bool              $condition
     * @param string|\Throwable $exception
     * @param array             ...$parameters
     *
     * @throws \Throwable
     *
     * @return null|bool
     */
    function throw_if(bool $condition, Throwable | string $exception, ...$parameters): ?bool
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return $condition;
    }
}

if (! function_exists('throw_unless')) {
    /**
     * @license https://github.com/laravel/framework
     * Throw the given exception unless the given condition is true.
     *
     * @param bool              $condition
     * @param string|\Throwable $exception
     * @param array             ...$parameters
     *
     * @throws \Throwable
     *
     * @return bool
     */
    function throw_unless(bool $condition, Throwable | string $exception, ...$parameters): bool
    {
        if (! $condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return $condition;
    }
}

/*
 * server 实例 基于 swoole server
 */
if (! function_exists('server')) {
    /**
     * @return \Swoole\Coroutine\Server|\Swoole\Server
     */
    function server(): Server | \Swoole\Coroutine\Server
    {
        return container()->get(ServerFactory::class)->getServer()->getServer();
    }
}

if (! function_exists('get_client_ip')) {
    function get_client_ip(): string
    {
        /**
         * @var RequestInterface $request
         */
        $request = container()->get(RequestInterface::class);
        $ip_addr = $request->getHeaderLine('x-forwarded-for');
        if (verify_ip($ip_addr)) {
            return $ip_addr;
        }
        $ip_addr = $request->getHeaderLine('remote-host');
        if (verify_ip($ip_addr)) {
            return $ip_addr;
        }
        $ip_addr = $request->getHeaderLine('x-real-ip');
        if (verify_ip($ip_addr)) {
            return $ip_addr;
        }
        $ip_addr = $request->getServerParams()['remote_addr'] ?? '0.0.0.0';
        if (verify_ip($ip_addr)) {
            return $ip_addr;
        }

        return '0.0.0.0';
    }
}

if (! function_exists('verify_ip')) {
    function verify_ip($realIp)
    {
        return filter_var($realIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
}

if (! function_exists('filter_emoji')) {
    function filter_emoji($str): string
    {
        $str     = preg_replace_callback(
            '/./u',
            static function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str
        );
        $cleaned = strip_tags($str);

        return htmlspecialchars(($cleaned));
    }
}

if (! function_exists('frame')) {
    /**
     * websocket frame 实例.
     */
    function frame(): Frame
    {
        return container()->get(Frame::class);
    }
}

if (! function_exists('cache')) {
    /**
     * 缓存实例 简单的缓存.
     */
    function cache(): CacheInterface
    {
        return container()->get(CacheInterface::class);
    }
}

if (! function_exists('stdLog')) {
    /**
     * 控制台日志.
     */
    function stdLog(): StdoutLoggerInterface
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

if (! function_exists('logger')) {
    /**
     * 文件日志.
     */
    function logger(string $name = 'app'): LoggerInterface
    {
        return Log::get($name);
    }
}

if (! function_exists('request')) {
    function request(): RequestInterface
    {
        return container()->get(RequestInterface::class);
    }
}

if (! function_exists('response')) {
    function response(): ResponseInterface
    {
        return container()->get(ResponseInterface::class);
    }
}

if (! function_exists('blank')) {
    /**
     * @license https://github.com/laravel/framework
     * Determine if the given value is "blank".
     *
     * @param mixed $value
     *
     * @return bool
     */
    function blank(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (! function_exists('filled')) {
    /**
     * @license https://github.com/laravel/framework
     * Determine if a value is "filled".
     *
     * @param mixed $value
     *
     * @return bool
     */
    function filled(mixed $value): bool
    {
        return ! blank($value);
    }
}

if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param int           $times
     * @param callable      $callback
     * @param int           $sleep
     * @param null|callable $when
     *
     * @throws \Exception
     *
     * @return mixed
     */
    function retry(int $times, callable $callback, int $sleep = 0, callable $when = null): mixed
    {
        $attempts = 0;

        beginning:
        $attempts++;
        --$times;

        try {
            return $callback($attempts);
        } catch (Exception $e) {
            if ($times < 1 || ($when && ! $when($e))) {
                throw $e;
            }

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (! function_exists('with')) {
    /**
     * @license https://github.com/laravel/framework
     * Return the given value, optionally passed through the given callback.
     *
     * @param mixed         $value
     * @param null|callable $callback
     *
     * @return mixed
     */
    function with(mixed $value, callable $callback = null): mixed
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

if (! function_exists('guid')) {
    /**
     * GUID在空间上和时间上具有唯一性，保证同一时间不同地方产生的数字不同。
     * 世界上的任何两台计算机都不会生成重复的 GUID 值
     */
    function guid(): string
    {
        $charId = strtoupper(md5(uniqid(Str::random(), true)));

        $hyphen = chr(45); // "-"

        return substr($charId, 0, 8) . $hyphen
               . substr($charId, 8, 4) . $hyphen
               . substr($charId, 12, 4) . $hyphen
               . substr($charId, 16, 4) . $hyphen
               . substr($charId, 20, 12);
    }
}
