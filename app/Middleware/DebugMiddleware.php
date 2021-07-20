<?php

declare(strict_types = 1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Middleware;

use App\Kernel\Log\AppendRequestProcessor;
use App\Kernel\Log\Log;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use function microtime;

class DebugMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @throws \Throwable
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $time = microtime(true);

        $requestId = Context::getOrSet(AppendRequestProcessor::LOG_REQUEST_ID_KEY, uniqid('', true));
        Context::getOrSet(AppendRequestProcessor::LOG_COROUTINE_ID_KEY, Coroutine::id());

        try {
            $response = $handler->handle($request);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            // 日志
            $time    = microtime(true) - $time;
            $context = [
                'url'  => $request->url(),
                'uri'  => $request->getUri()->getPath(),
                'time' => $time,
            ];

            if ($query = $request->getQueryParams()) {
                $context['query'] = $query;
            }

            if ($inputs = $this->getRequestInputArray()) {
                $context['request'] = $inputs;
            }

            if ($customData = $this->getCustomData()) {
                $context['custom'] = $customData;
            }
            if (isset($response)) {
                $context['response'] = $this->getResponseString($response);
            }
            if (isset($exception) && $exception instanceof Throwable) {
                $context['headers']   = $this->getHeaders($request);
                $context['exception'] = [
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'code'    => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ];
            }

            if ($time > 1 || isset($exception)) {
                Log::get('request')->error($requestId, $context);
            } else {
                Log::get('request')->info($requestId, $context);
            }
        }

        return $response;
    }

    protected function getHeaders(ServerRequestInterface $request): array
    {
        $headers        = $request->getHeaders();
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
        $logHeaders     = [];
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
            'text/html'                => 'html',
            'image/x-icon'             => 'icon',
            'application/x-javascript' => 'js',
            'text/css'                 => 'css',
            'image/svg'                => 'svg',
            'image/jpeg'               => 'jpg',
            'image/webp'               => 'png',
            'image/png'                => 'png',
            'image/gif'                => 'gif',
            'image/bmp'                => 'bmp',
        ];
        $type        = $response->getHeaderLine('content-type');
        foreach ($contentType as $k => $v) {
            if (Str::startsWith($type, $k)) {
                return $v;
            }
        }

        return (string) $response->getBody();
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

    protected function getCustomData(): string
    {
        return '';
    }
}
