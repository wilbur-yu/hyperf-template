<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Middleware;

use App\Exception\AuthorizationException;
use App\Exception\BusinessException;
use App\Exception\DecryptException;
use App\Exception\Formatter\AppFormatter;
use WilburYu\HyperfCacheExt\Exception\CounterRateLimitException;
use App\Exception\NotFoundException;
use App\Kernel\Log\AppendRequestProcessor;
use App\Kernel\Log\Log;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function microtime;

class DebugMiddleware implements MiddlewareInterface
{
    protected array $dontReport = [
        DecryptException::class,
        NotFoundHttpException::class,
        HttpException::class,
        NotFoundException::class,
        BusinessException::class,
        AuthorizationException::class,
        DecryptException::class,
        CounterRateLimitException::class,
    ];

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);

        $requestId = Context::getOrSet(AppendRequestProcessor::LOG_REQUEST_ID_KEY, uniqid('', true));
        Context::getOrSet(AppendRequestProcessor::LOG_COROUTINE_ID_KEY, Coroutine::id());
        try {
            $response = $handler->handle($request);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            // 日志
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000);
            $context = [
                'url' => $request->url(),
                'uri' => $request->getUri()->getPath(),
                'method' => $request->getMethod(),
                'time' => $duration.'ms',
            ];
            $context['query'] = $request->getQueryParams();
            $context['request'] = $this->getRequestInputArray();
            $context['headers'] = $this->getHeaders($request);

            isset($response) && $context['response'] = $this->getResponseString($response);

            isset($exception) && !$this->shouldntReport($exception)
            && $context['exception'] = make(AppFormatter::class)->format($exception, !env_is_production());

            if ($duration >= 300 || isset($context['exception'])) {
                Log::get('request')->error($requestId, $context);
            } else {
                Log::get('request')->debug($requestId, $context);
            }
        }

        return $response;
    }

    protected function shouldntReport(Throwable $e): bool
    {
        $dontReport = $this->dontReport;

        $isShouldnt = !is_null(
            Arr::first($dontReport, static function ($type) use ($e) {
                return $e instanceof $type;
            })
        );

        return env_is_production() && $isShouldnt;
    }

    protected function getHeaders(ServerRequestInterface $request): array
    {
        $headers = $request->getHeaders();
        $onlyHeaderKeys = [
            'content-type',
            'user-agent',
            'sign',
            'token',
            'x-token',
            'Authorization',
            'x-real-ip',
            'x-forwarded-for',
            'cookie',
        ];
        $logHeaders = [];
        foreach ($onlyHeaderKeys as $value) {
            if (isset($headers[$value])) {
                $logHeaders[$value] = $headers[$value];
            }
        }

        return $logHeaders;
    }

    protected function getResponseString(ResponseInterface $response): string
    {
        $contentType = [
            'text/html' => 'html',
            'image/x-icon' => 'icon',
            'application/x-javascript' => 'js',
            'text/css' => 'css',
            'image/svg' => 'svg',
            'image/jpeg' => 'jpg',
            'image/webp' => 'png',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
        ];
        $type = $response->getHeaderLine('content-type');
        foreach ($contentType as $k => $v) {
            if (Str::startsWith($type, $k)) {
                return $v;
            }
        }

        return (string)$response->getBody();
    }

    protected function getRequestString(): string
    {
        $data = $this->container->get(Request::class)->all();

        return Json::encode($data);
    }

    protected function getRequestInputArray(): array
    {
        return $this->container->get(Request::class)->post();
    }
}
