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
namespace App\Kernel\Server;

use App\Constants\HttpCode;
use App\Kernel\Contract\ResponseInterface;
use App\Kernel\Log\AppendRequestProcessor;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response as BaseResponse;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response extends BaseResponse implements ResponseInterface
{
    protected array $customHeaders = [];

    private string $author = 'wenber.yu@creative-life.club';

    public function success(
        $data = [],
        string $message = 'success',
        int $code = HttpCode::HTTP_OK
    ): PsrResponseInterface {
        return $this->formatting($code, $message, $data);
    }

    public function fail(int $code, string $message = '', array $errors = [], array $data = []): PsrResponseInterface
    {
        if (empty($message)) {
            $message = HttpCode::getMessage($code) ?? 'error';
        }

        if (config('app_env') !== 'dev') {
            $errors = [];
        }

        return $this->formatting($code, $message, [], $errors);
    }

    public function custom(array $data = []): PsrResponseInterface
    {
        return $this->json($data);
    }

    public function cookie(
        string $name,
        string $value = '',
        $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        bool $raw = false,
        ?string $sameSite = null
    ): self {
        $cookie   = new Cookie(
            $name,
            $value,
            $expire,
            $path,
            $domain,
            $secure,
            $httpOnly,
            $raw,
            $sameSite
        );
        $response = $this->getResponse()->withCookie($cookie);
        Context::set(ResponseInterface::class, $response);

        return $this;
    }

    public function handleException(HttpException $throwable): PsrResponseInterface
    {
        return $this->getResponse()
            ->withAddedHeader('Server', config('app_name'))
            ->withStatus($throwable->getStatusCode())
            ->withBody(new SwooleStream($throwable->getMessage()));
    }

    protected function formatting(int $code, string $message, $data = [], $errors = []): PsrResponseInterface
    {
        $body = [
            'request_id' => Context::get(AppendRequestProcessor::LOG_REQUEST_ID_KEY),
            'code'       => $code,
            'message'    => $message,
        ];

        ! empty($data) && $body['data'] = $data;

        ! empty($errors) && $body['errors'] = $errors;

        $body = $this->toJson($body);

        return $this->withCustomHeaders(['content-type' => 'application/json; charset=utf-8'])
            ->withBody(new SwooleStream($body));
    }

    protected function withCustomHeaders(array $headers): PsrResponseInterface
    {
        $config  = config('app_response_headers');
        $headers = array_merge($config, $headers);

        $response = $this->getResponse();
        foreach ($headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }
        Context::set(ResponseInterface::class, $response);

        return $response;
    }
}
