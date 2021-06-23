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
namespace App\Support;

use App\Constants\ErrorCode;
use App\Kernel\Log\AppendRequestProcessor;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class ApiResponse
{
    protected ContainerInterface $container;

    private array $headers = [
        'Author' => 'wenber.yu@creative-life.club',
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function withHeader(string $key, $value): self
    {
        $this->headers += [$key => $value];

        return $this;
    }

    /**
     * @param array $headers header数组：[key1 => value1, key2 => value2]
     *
     * @return $this
     */
    public function withHeaders(array $headers = []): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    public function success(
        $data = [],
        string $message = 'success',
        int $code = ErrorCode::HTTP_OK
    ): PsrResponseInterface {
        return $this->formatting($code, $message, $data);
    }

    public function fail(int $code, string $message = '', array $errors = []): PsrResponseInterface
    {
        if (empty($message)) {
            $message = ErrorCode::getMessage($code) ?? 'error';
        }

        if (config('app_env') !== 'dev') {
            $errors = [];
        }

        return $this->formatting($code, $message, [], $errors);
    }

    public function parkingFail(string $message): PsrResponseInterface
    {
        return $this->response()->json([
            'status'    => 'fail',
            'errorCode' => $message,
        ]);
    }

    public function parkingSuccess($timestamp): PsrResponseInterface
    {
        return $this->response()->json([
            'status' => 'success',
            'datas'  => [
                'timeStamp' => $timestamp,
            ],
        ]);
    }

    public function redirect(string $url, int $status = 302): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Location', $url)
            ->withStatus($status);
    }

    public function cookie(Cookie $cookie): self
    {
        $response = $this->response()->withCookie($cookie);
        Context::set(ResponseInterface::class, $response);

        return $this;
    }

    public function handleException(HttpException $throwable): PsrResponseInterface
    {
        return $this->response()
            ->withAddedHeader('Server', 'Wey')
            ->withStatus($throwable->getStatusCode())
            ->withBody(new SwooleStream($throwable->getMessage()));
    }

    public function response(): ResponseInterface
    {
        if (! Context::has(ResponseInterface::class)) {
            $response = $this->container->get(ResponseInterface::class);
            Context::set(ResponseInterface::class, $response);
        }

        return Context::override(ResponseInterface::class, function (ResponseInterface $response) {
            $newResponse = $response;
            foreach ($this->headers as $key => $value) {
                $newResponse = $newResponse->withHeader($key, $value);
            }

            return $newResponse;
        });
    }

    protected function formatting(
        int $code,
        string $message,
        $data = [],
        $errors = []
    ): PsrResponseInterface {
        $response = [
            'request_id' => Context::get(AppendRequestProcessor::LOG_REQUEST_ID_KEY),
            'code'       => $code,
            'message'    => $message,
        ];

        ! empty($data)   && $response['data']     = $data;
        ! empty($errors) && $response['errors']   = $errors;

        return $this->response()->json($response);
    }
}
