<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Exception\Handler;

use App\Constants\BusCode;
use App\Constants\HttpCode;
use App\Exception\AuthorizationException;
use App\Exception\Formatter\AppFormatter;
use WilburYu\HyperfCacheExt\Exception\CounterRateLimitException;
use App\Kernel\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    #[Inject]
    protected ResponseInterface $response;

    public function handle(Throwable $throwable, PsrResponseInterface $response): PsrResponseInterface
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
            $message = BusCode::getMessage($code);
        }

        return $this->response->withAddedHeaders($headers ?? [])->fail(
            $status ?: BusCode::SERVER_ERROR,
            $message ?? $throwable->getMessage(),
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
        return config('app_debug', false)
            ? make(AppFormatter::class)->format($throwable, !env_is_production()) :
            ['message' => $this->isHttpException($throwable) ? $throwable->getMessage() : 'Server Error',];
    }

    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
    }
}
