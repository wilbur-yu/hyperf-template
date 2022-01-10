<?php

declare(strict_types=1);
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

namespace App\Exception\Handler;

use App\Constants\BusCode;
use App\Constants\HttpCode;
use App\Exception\AuthorizationException;
use App\Exception\Formatter\AppFormatter;
use App\Kernel\Contract\ResponseInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as BaseResponseInterface;
use Throwable;
use WilburYu\HyperfCacheExt\Exception\CounterRateLimitException;

class AppExceptionHandler extends ExceptionHandler
{
    protected ResponseInterface $response;

    public function __construct(ContainerInterface $container)
    {
        $this->response = $container->get(ResponseInterface::class);
    }

    public function handle(Throwable $throwable, BaseResponseInterface $response): BaseResponseInterface
    {
        $this->stopPropagation();
        $status = $throwable->getCode();
        $status = is_string($status) ? (int)$status : $status;
        if ($throwable instanceof AuthorizationException) {
            $code = HttpCode::UNAUTHORIZED;
        }
        if ($this->isHttpException($throwable)) {
            $code = $throwable->getStatusCode();
        }

        if ($throwable instanceof CounterRateLimitException) {
            $headers = $throwable->getHeaders();
            $code = $throwable->getCode();
        }

        return $this->response
            ->withAddedHeaders($headers ?? [])
            ->fail(
                $status ?: BusCode::SERVER_ERROR,
                $throwable->getMessage(),
                $this->convertExceptionToArray($throwable),
                code: $code ?? HttpCode::SERVER_ERROR
            );
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    protected function convertExceptionToArray(Throwable $throwable): array
    {
        $format = make(AppFormatter::class)->format($throwable, !env_is_production());

        return config('app_debug', false) ? $format : [
            'message' => $this->isHttpException($throwable) ? $throwable->getMessage() : 'Server Error',
        ];
    }

    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
    }
}
