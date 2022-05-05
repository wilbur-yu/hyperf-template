<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Middleware;

use App\Kernel\Log\AppendRequestProcessor;
use App\Kernel\Log\Log;
use App\Report\Notifier;
use App\Support\Trait\HasUser;
use Carbon\Carbon;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function microtime;

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
        $this->record($request);

        defer(function () {
            try {
                $this->log();
            } catch (Throwable $e) {
                Log::get('debug.middleware')->error($e->getMessage());
            }
        });

        return $handler->handle($request);
    }

    public function log(): void
    {
        $endTime = microtime(true);
        $context = Context::get(AppendRequestProcessor::LOG_LIFECYCLE_KEY) ?? [];
        $duration = round(($endTime - $context['trigger_time']) * 1000);

        $context['duration'] = $duration.'ms';
        $context['trigger_time'] = Carbon::createFromTimestamp($context['trigger_time'])->toDateTimeString();

        $response = Context::get(ResponseInterface::class);
        $context['response'] = $this->getResponseToArray($response);
        isset($context['exception']) && make(Notifier::class)->exceptionReport($context, $context['exception']);
        if ($duration >= 500) {
            Log::get('request')->error(Context::get(AppendRequestProcessor::LOG_REQUEST_ID_KEY), $context);
        } else {
            Log::get('request')->debug(Context::get(AppendRequestProcessor::LOG_COROUTINE_ID_KEY), $context);
        }
    }

    /**
     * @param  \Psr\Http\Message\ServerRequestInterface|null  $request
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return void
     */
    protected function record(?ServerRequestInterface $request): void
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
            'duration' => '',
        ];
        $context['headers'] = $this->getHeaders($request);
        $context['query'] = $request?->getQueryParams();
        $context['request'] = $this->container->get(RequestInterface::class)->post();
        $context = array_merge($context, $this->getUser());

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
