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

namespace App\Kernel\Server;

use App\Constants\BusCode;
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
    public function success(
        $data = [],
        string $message = 'success',
        int $code = HttpCode::HTTP_OK
    ): PsrResponseInterface {
        return $this->formatting($code, $message, $data);
    }

    public function fail(
        int $status = BusCode::SUCCESS,
        string $message = '',
        array $errors = [],
        $data = [],
        int $code = HttpCode::HTTP_OK,
    ): PsrResponseInterface {
        if (empty($message)) {
            $message = BusCode::getMessage($code) ?? 'bus error';
        }

        if (config('app_env') !== 'dev') {
            $errors = [];
        }

        return $this->formatting($code, $message, $data, $status, $errors);
    }

    public function custom(array $data = []): PsrResponseInterface
    {
        return $this->withCustomHeaders()->json($data);
    }

    public function handleException(HttpException $throwable): PsrResponseInterface
    {
        return $this->getResponse()
            ?->withAddedHeader('Server', config('app_name'))
            ->withStatus($throwable->getStatusCode())
            ->withBody(new SwooleStream($throwable->getMessage()));
    }

    protected function formatting(
        int $code,
        string $message,
        $data,
        int $status = BusCode::SUCCESS,
        array $errors = []
    ): PsrResponseInterface {
        $body = [
            'request_id' => Context::get(AppendRequestProcessor::LOG_REQUEST_ID_KEY),
            'status' => $status,
            'message' => $message,
        ];

        !empty($data) && $body['data'] = $data;

        !empty($errors) && $body['errors'] = $errors;

        return $this->withCustomHeaders()->withStatus($code)->json($body);
    }

    protected function withCustomHeaders(array $headers = []): PsrResponseInterface
    {
        $config = config('app_response_headers');
        $headers = array_merge($config, $headers);

        $response = $this->getResponse();
        foreach ($headers as $key => $value) {
            $response = $response?->withHeader($key, $value);
        }
        Context::set(ResponseInterface::class, $response);

        return $response;
    }
}
