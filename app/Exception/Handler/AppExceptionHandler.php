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
use App\Exception\DecryptException;
use App\Exception\Formatter\ExceptionFormatter;
use App\Exception\NotFoundException;
use App\Kernel\Http\Response;
use App\Kernel\Log\AppendRequestProcessor;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\Utils\Arr;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;
use WilburYu\HyperfCacheExt\Exception\CounterRateLimiterException;

class AppExceptionHandler extends ExceptionHandler
{
    use ExceptionFormatter;

    #[Inject]
    protected Response $response;

    protected array $dontReportException = [
        DecryptException::class,
        NotFoundHttpException::class,
        HttpException::class,
        NotFoundException::class,
        // BusinessException::class,
        AuthorizationException::class,
        CounterRateLimiterException::class,
    ];

    protected array $dontReportCode = [
    ];

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

        if ($throwable instanceof CounterRateLimiterException) {
            $headers = $throwable->getHeaders();
            $code = $throwable->getCode();
            $message = BusCode::getMessage($code);
        }

        $this->alarm($throwable);

        return $this->response->withAddedHeaders($headers ?? [])->fail(
            $status ?: BusCode::SERVER_ERROR,
            $message ?? $throwable->getMessage(),
            $this->convertExceptionToArray($throwable),
            code: $code ?? HttpCode::SERVER_ERROR
        );
    }

    protected function alarm($throwable): void
    {
        if ($this->shouldReport($throwable)) {
            Context::override(AppendRequestProcessor::LOG_LIFECYCLE_KEY, static function ($context) use ($throwable) {
                $context['exception'] = $throwable;

                return $context;
            });
            // make(Notifier::class)->exceptionReport($context, $throwable);
        }
    }

    protected function shouldReport(Throwable $e): bool
    {
        if (!config('app_exception_alarm_enable')) {
            return false;
        }

        $isDontReportException = is_null(
            Arr::first($this->dontReportException, static function ($type) use ($e) {
                return $e instanceof $type;
            })
        );
        $isDontReportCode = is_null(
            Arr::first($this->dontReportCode, static function ($code) use ($e) {
                return $e->getCode() === $code;
            })
        );

        return $isDontReportCode && $isDontReportException;
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    protected function convertExceptionToArray(Throwable $throwable): array
    {
        return config('app_debug', false)
            ? $this->format($throwable, !env_is_production()) :
            ['message' => $this->isHttpException($throwable) ? $throwable->getMessage() : 'Server Error',];
    }

    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
    }
}
