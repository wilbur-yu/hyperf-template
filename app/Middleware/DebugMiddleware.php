<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu
 */

namespace App\Middleware;

use Carbon\Carbon;
use App\Kernel\Log\AppendRequestProcessor;
use App\Kernel\Log\Log;
use App\Report\Notifier;
use App\Support\Trait\HasUser;
use Hyperf\Context\Context;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function defer;
use function microtime;
use function str_contains;

class DebugMiddleware implements MiddlewareInterface
{
    use HasUser;

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @param  \Psr\Http\Message\ServerRequestInterface  $request
     * @param  \Psr\Http\Server\RequestHandlerInterface  $handler
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'HEAD' && $request->getUri()->getPath() === '/api') {
            return $handler->handle($request);
        }
        Context::getOrSet(AppendRequestProcessor::LOG_REQUEST_ID_KEY, uniqid('', true));
        Context::getOrSet(AppendRequestProcessor::LOG_COROUTINE_ID_KEY, Coroutine::id());
        try {
            $this->collecter($request);
        } finally {
            defer(function () {
                try {
                    $this->record();
                } catch (Throwable $e) {
                    Log::get('debug.middleware')->error($e->getMessage().' '.format_throwable($e));
                }
            });
        }

        return $handler->handle($request);
    }

    public function record(): void
    {
        $endTime = microtime(true);
        $context = Context::get(AppendRequestProcessor::LOG_LIFECYCLE_KEY) ?? [];
        $duration = 0;
        if (isset($context['trigger_time'])) {
            $duration = round(($endTime - $context['trigger_time']) * 1000);
            $context['duration'] = $duration.'ms';
            $context['trigger_time'] = Carbon::createFromTimestamp($context['trigger_time'])->toDateTimeString();
        }

        $response = Context::get(ResponseInterface::class);
        $context['response'] = $this->getResponseToArray($response);
        isset($context['exception']) && make(Notifier::class)->reportForException($context, $context['exception']);
        if (isset($context['exception'])) {
            $logLevel = 'error';
        } elseif (environment()->is('test', 'local')) {
            $logLevel = 'info';
        } elseif ($duration >= 500) {
            $logLevel = 'warning';
        } else {
            $logLevel = 'debug';
        }
        Log::get('request')->{$logLevel}(Context::get(AppendRequestProcessor::LOG_REQUEST_ID_KEY), $context);
    }

    /**
     * @param  \Psr\Http\Message\ServerRequestInterface|null  $request
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return void
     */
    protected function collecter(?ServerRequestInterface $request): void
    {
        $startTime = microtime(true);
        $context = [
            'app_name' => config('app_name'),
            'app_env' => config('app_env'),
            'trigger_time' => $startTime,
            'usage_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 1).'M',
            'url' => $request?->url(),
            'uri' => $request?->getUri()->getPath(),
            'method' => $request?->getMethod(),
            'client_ip' => get_client_ip(),
            'duration' => '',
            'headers' => $this->getHeaders($request),
            'query' => $request?->getQueryParams(),
            'payload' => $request?->getParsedBody(),
            'user' => $this->getUser(),
        ];
        Context::set(AppendRequestProcessor::LOG_LIFECYCLE_KEY, $context);
    }

    protected function getUser(): array
    {
        if (self::isLogin()) {
            $user = self::user();

            return [
                'user' => [
                    'id' => $user->id,
                    'nickname' => $user->nickname,
                    'role' => $user->role,
                    'ski_resort_id' => $user->ski_resort_id,
                    'tel' => $user->tel,
                ],
            ];
        }

        return [];
    }

    protected function getHeaders(?ServerRequestInterface $request): array
    {
        if ($request === null) {
            return [];
        }
        $onlyHeaderKeys = [
            'content-type',
            'user-agent',
            'sign',
            'token',
            'x-token',
            'authorization',
            'x-real-ip',
            'x-forwarded-for',
            'cookie',
        ];
        $logHeaders = [];
        foreach ($onlyHeaderKeys as $value) {
            if ($request->getHeaderLine($value)) {
                $logHeaders[$value] = $request->getHeaderLine($value);
            }
        }

        return array_filter($logHeaders);
    }

    protected function getResponseToArray(?ResponseInterface $response): array
    {
        if ($response === null) {
            return [];
        }

        $type = $response->getHeaderLine('content-type');
        if (str_contains($type, 'application/json')) {
            $data = Json::decode($response->getBody()->getContents());
        } else {
            $data = (array)$response->getBody();
        }

        if (isset($data['debug']) || isset($data['soar'])) {
            unset($data['debug'], $data['soar']);
        }

        return $data;
    }
}
