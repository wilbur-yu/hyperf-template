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
use App\Kernel\Contract\ResponseInterface;
use Hyperf\Di\Exception\CircularDependencyException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as BaseResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    protected ResponseInterface $response;

    public function __construct(ContainerInterface $container)
    {
        $this->response = $container->get(ResponseInterface::class);
    }

    public function handle(Throwable $throwable, BaseResponseInterface $response): BaseResponseInterface
    {
        switch (true) {
            case $throwable instanceof HttpException:
                return $this->response->handleException($throwable);
            case $throwable instanceof BusinessException:
                return $this->response->fail(
                    $throwable->getCode(),
                    $throwable->getMessage(),
                    HttpCode::HTTP_OK,
                    $this->convertExceptionToArray($throwable)
                );
            case $throwable instanceof CircularDependencyException:
                return $this->response->fail(HttpCode::SERVER_ERROR, $throwable->getMessage());
        }
        $this->stopPropagation();

        return $this->response->fail(
            HttpCode::SERVER_ERROR,
            $throwable->getMessage(),
            HttpCode::HTTP_OK,
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
