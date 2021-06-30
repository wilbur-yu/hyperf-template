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
namespace App\Exception\Handler;

use App\Constants\HttpCode;
use App\Exception\BusinessException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $newResponse = app()->get(\App\Kernel\Contract\ResponseInterface::class);

        if ($throwable instanceof HttpException) {
            return $newResponse->handleException($throwable);
        }

        if ($throwable instanceof BusinessException) {
            return $newResponse->fail(
                $throwable->getCode(),
                $throwable->getMessage(),
                $this->convertExceptionToArray($throwable)
            );
        }
        $this->stopPropagation();

        return $newResponse->fail(
            HttpCode::SERVER_ERROR,
            $throwable->getMessage(),
            $this->convertExceptionToArray($throwable)
        );
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    protected function convertExceptionToArray(Throwable $throwable): array
    {
        return config('app_debug', false) ? [
            'message'   => $throwable->getMessage(),
            'exception' => get_class($throwable),
            'file'      => $throwable->getFile(),
            'line'      => $throwable->getLine(),
            'trace'     => collect($throwable->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isHttpException($throwable) ? $throwable->getMessage() : 'Server Error',
        ];
    }

    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
    }
}
