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

        return $this->getResponse()
            ->withHeader('Author', $this->author)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody(new SwooleStream($body));
    }
}