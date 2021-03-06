<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

use App\Kernel\Contract\ResponseInterface;
use App\Kernel\Log\Log;
use App\Kernel\Http\RouteCollector;
use App\Support\AuthCode;
use Carbon\Carbon;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Server\ServerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Str;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use HyperfExt\Auth\Contracts\AuthManagerInterface;
use HyperfExt\Auth\Contracts\GuardInterface;
use HyperfExt\Auth\Contracts\StatefulGuardInterface;
use HyperfExt\Auth\Contracts\StatelessGuardInterface;
use JetBrains\PhpStorm\Pure;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server;
use Swoole\WebSocket\Frame;

if (!function_exists('env_is_local')) {
    function env_is_local(): bool
    {
        return config('app_env') === 'dev';
    }
}

if (!function_exists('env_is_production')) {
    function env_is_production(): bool
    {
        return config('app_env') === 'prod';
    }
}

if (!function_exists('env_is_test')) {
    function env_is_test(): bool
    {
        return config('app_env') === 'test';
    }
}

if (!function_exists('encrypt')) {
    function encrypt($data, int $expiry = 0): string
    {
        return di(AuthCode::class)->encrypt($data, $expiry);
    }
}

if (!function_exists('decrypt')) {
    function decrypt(string $encrypt, ?string $message = null): int|array|string
    {
        return di(AuthCode::class)->decrypt($encrypt, $message);
    }
}

if (!function_exists('locale')) {
    /**
     * @return string
     */
    function locale(): string
    {
        return di(TranslatorInterface::class)->getLocale();
    }
}
if (!function_exists('array_reduces')) {
    /**
     * @param null $carry
     *
     * @return mixed|null
     */
    function array_reduces(array $array, callable $callback, $carry = null): mixed
    {
        foreach ($array as $key => $value) {
            $carry = $callback($carry, $value, $key);
        }

        return $carry;
    }
}
/**
 * Transform the given value if it is present.
 *
 * @param  mixed       $value
 * @param  callable    $callback
 * @param  mixed|null  $default
 *
 * @return mixed|null
 */
function transform(mixed $value, callable $callback, mixed $default = null): mixed
{
    if (filled($value)) {
        return $callback($value);
    }

    if (is_callable($default)) {
        return $default($value);
    }

    return $default;
}

if (!function_exists('auth')) {
    /**
     * hyperf-ext/auth: Auth??????????????????.
     */
    function auth(?string $guard = null): StatefulGuardInterface|GuardInterface|StatelessGuardInterface
    {
        return make(AuthManagerInterface::class)->guard($guard);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $variables = [], string $server = 'http'): string
    {
        return di(RouteCollector::class)->getPath($name, $variables, $server);
    }
}

if (!function_exists('format_duration')) {
    /**
     * Format duration.
     */
    function format_duration(float $seconds): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000).'??s';
        }

        if ($seconds < 1) {
            return round($seconds * 1000, 2).'ms';
        }

        return round($seconds, 2).'s';
    }
}

if (!function_exists('hide_str')) {
    /**
     * ?????????????????????????????????$re????????????.
     *
     * @param  null|string  $string  ?????????????????????
     * @param  int          $start  ????????????????????????????????????
     *                            ?????? - ?????????????????????????????????
     *                            ?????? - ??????????????????????????????????????????
     *                            0 - ??????????????????????????????????????????
     * @param  int          $length  ?????????????????????????????????????????????????????????????????????????????????
     *                            ?????? - ??? start ???????????????????????????
     *                            ?????? - ????????????????????????
     * @param  string       $re  ?????????
     *
     * @return bool|string ?????????????????????
     */
    function hide_str(?string $string, int $start = 0, int $length = 0, string $re = '*'): bool|string
    {
        if (empty($string)) {
            return '';
        }
        $strArr = [];
        $mbStrLen = mb_strlen($string);
        //??????????????????????????????
        while ($mbStrLen) {
            $strArr[] = mb_substr($string, 0, 1, 'utf8');
            $string = mb_substr($string, 1, $mbStrLen, 'utf8');
            $mbStrLen = mb_strlen($string);
        }
        $strLen = count($strArr);
        $begin = $start >= 0 ? $start : ($strLen - abs($start));
        $end = $last = $strLen - 1;
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

if (!function_exists('di')) {
    function di(string $class)
    {
        return container()->get($class);
    }
}

if (!function_exists('order_no')) {
    /**
     * ??????????????????????????????????????????????????????QPS<=1w??????????????????????????????
     */
    function order_no(string $prefix = ''): string
    {
        return $prefix.date('YmdHis').
               substr(
                   implode(
                       '',
                       array_map(
                           'ord',
                           str_split(
                               substr(
                                   uniqid('', true),
                                   7,
                                   13
                               )
                           )
                       )
                   ),
                   0,
                   8
               );
    }
}

if (!function_exists('container')) {
    #[Pure]
    function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}

if (!function_exists('format_throwable')) {
    function format_throwable(Throwable $throwable): string
    {
        return di(FormatterInterface::class)->format($throwable);
    }
}

if (!function_exists('throw_if')) {
    /**
     * @license https://github.com/laravel/framework
     * Throw the given exception if the given condition is true.
     *
     * @param  bool               $condition
     * @param  string|\Throwable  $exception
     * @param  array              ...$parameters
     *
     * @return bool|null
     * @throws \Throwable
     */
    function throw_if(bool $condition, Throwable|string $exception, ...$parameters): ?bool
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return false;
    }
}

if (!function_exists('throw_unless')) {
    /**
     * @license https://github.com/laravel/framework
     * Throw the given exception unless the given condition is true.
     *
     * @param  bool               $condition
     * @param  string|\Throwable  $exception
     * @param  array              ...$parameters
     *
     * @return bool
     * @throws \Throwable
     */
    function throw_unless(bool $condition, Throwable|string $exception, ...$parameters): bool
    {
        if (!$condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return true;
    }
}

// server ?????? ?????? swoole server
if (!function_exists('server')) {
    function server(): Server|Swoole\Coroutine\Server
    {
        return di(ServerFactory::class)->getServer()->getServer();
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip(): string
    {
        /**
         * @var RequestInterface $request
         */
        $request = di(RequestInterface::class);
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

if (!function_exists('verify_ip')) {
    function verify_ip($realIp)
    {
        return filter_var($realIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
}

if (!function_exists('filter_emoji')) {
    function filter_emoji(?string $str = null): string|null
    {
        if ($str === null) {
            return $str;
        }
        $str = preg_replace_callback(
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

if (!function_exists('frame')) {
    /**
     * websocket frame ??????.
     * @return \Swoole\WebSocket\Frame
     */
    function frame(): Frame
    {
        return di(Frame::class);
    }
}

if (!function_exists('stdLog')) {
    /**
     * @return \Hyperf\Contract\StdoutLoggerInterface
     */
    function stdLog(): StdoutLoggerInterface
    {
        return di(StdoutLoggerInterface::class);
    }
}

if (!function_exists('logger')) {
    /**
     * ????????????.
     */
    function logger(string $name = 'app'): LoggerInterface
    {
        return Log::get($name);
    }
}

if (!function_exists('request')) {
    function request(): RequestInterface
    {
        return di(RequestInterface::class);
    }
}

if (!function_exists('response')) {
    function response(): ResponseInterface
    {
        return di(ResponseInterface::class);
    }
}

if (!function_exists('blank')) {
    /**
     * @license https://github.com/laravel/framework
     * Determine if the given value is "blank".
     */
    function blank(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return '' === trim($value);
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return 0 === count($value);
        }

        return empty($value);
    }
}

if (!function_exists('filled')) {
    /**
     * @license https://github.com/laravel/framework
     * Determine if a value is "filled".
     */
    #[Pure]
    function filled(mixed $value): bool
    {
        return !blank($value);
    }
}

if (!function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @throws \Exception
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
            if ($times < 1 || ($when && !$when($e))) {
                throw $e;
            }

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (!function_exists('with')) {
    /**
     * @license https://github.com/laravel/framework
     * Return the given value, optionally passed through the given callback.
     */
    function with(mixed $value, callable $callback = null): mixed
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

if (!function_exists('guid')) {
    /**
     * GUID????????????????????????????????????????????????????????????????????????????????????????????????
     * ????????????????????????????????????????????????????????? GUID ???
     */
    function guid(): string
    {
        $charId = strtoupper(md5(uniqid(Str::random(), true)));

        $hyphen = chr(45); // "-"

        return substr($charId, 0, 8).$hyphen
               .substr($charId, 8, 4).$hyphen
               .substr($charId, 12, 4).$hyphen
               .substr($charId, 16, 4).$hyphen
               .substr($charId, 20, 12);
    }
}
if (!function_exists('info')) {
    /**
     * @license https://github.com/friendsofhyperf/helpers
     *
     * @param  string  $message
     * @param  array   $context
     * @param  bool    $backtrace
     */
    function info(string $message, array $context = [], bool $backtrace = false)
    {
        if ($backtrace) {
            $traces = debug_backtrace();
            $context['backtrace'] = sprintf('%s:%s', $traces[0]['file'], $traces[0]['line']);
        }

        logger()->info($message, $context);
    }
}
if (!function_exists('now')) {
    /**
     * @license https://github.com/friendsofhyperf/helpers
     * Create a new Carbon instance for the current time.
     *
     * @param  \DateTimeZone|string|null  $tz
     *
     * @return \Carbon\Carbon
     */
    function now(DateTimeZone|string $tz = null): Carbon
    {
        return Carbon::now($tz);
    }
}
if (!function_exists('object_get')) {
    /**
     * @license https://github.com/friendsofhyperf/helpers
     * Get an item from an object using "dot" notation.
     *
     * @param  object       $object
     * @param  string|null  $key
     * @param  mixed|null   $default
     *
     * @return mixed
     */
    function object_get(object $object, ?string $key, mixed $default = null): mixed
    {
        if (is_null($key) || trim($key) === '') {
            return $object;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}
if (!function_exists('session')) {
    /**
     * @license https://github.com/friendsofhyperf/helpers
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @return \Hyperf\Contract\SessionInterface
     */
    function session(): SessionInterface
    {
        return di(SessionInterface::class);
    }
}
if (!function_exists('today')) {
    /**
     * @license https://github.com/friendsofhyperf/helpers
     * Create a new Carbon instance for the current date.
     *
     * @param  \DateTimeZone|string|null  $tz
     *
     * @return \Carbon\Carbon
     */
    function today(DateTimeZone|string $tz = null): Carbon
    {
        return Carbon::today($tz);
    }
}
if (!function_exists('validator')) {
    /**
     * @license https://github.com/friendsofhyperf/helpers
     * Create a new Validator instance.
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     *
     * @return \Hyperf\Contract\ValidatorInterface|\Hyperf\Validation\Contract\ValidatorFactoryInterface
     */
    function validator(
        array $data = [],
        array $rules = [],
        array $messages = [],
        array $customAttributes = []
    ): ValidatorFactoryInterface|ValidatorInterface {
        /** @var \Hyperf\Validation\Contract\ValidatorFactoryInterface $factory */
        $factory = di(ValidatorFactoryInterface::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}
if (!function_exists('event')) {
    /**
     * @license https://github.com/friendsofhyperf/helpers
     * Dispatch an event and call the listeners.
     *
     * @return \Psr\EventDispatcher\EventDispatcherInterface
     */
    function event(): EventDispatcherInterface
    {
        return di(EventDispatcherInterface::class);
    }
}

if (!function_exists('job')) {
    function job(string $driver = 'default'): DriverInterface
    {
        return di(DriverFactory::class)->get($driver);
    }
}
