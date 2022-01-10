<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Kernel\Http;

use App\Constants\BusCode;
use App\Constants\HttpCode;
use App\Kernel\Contract\ResponseInterface as CustomResponseInterface;
use App\Kernel\Log\AppendRequestProcessor;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Response as BaseResponse;
use Hyperf\Paginator\AbstractPaginator;
use Hyperf\Resource\Json\JsonResource;
use Hyperf\Resource\Json\ResourceCollection;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response extends BaseResponse implements CustomResponseInterface
{
    public function success(
        mixed $data = [],
        string $message = 'success',
        int $code = HttpCode::OK
    ): PsrResponseInterface {
        if ($data instanceof ResourceCollection) {
            $data = $this->formatResourceCollection(...func_get_args());
        }

        if ($data instanceof AbstractPaginator) {
            $data = $this->formatPaginated($data);
        }

        if ($data instanceof JsonResource) {
            $data = $this->formatResource(...func_get_args());
        }

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return $this->formatting($code, $message, $code, $data);
    }

    protected function formatPaginated(AbstractPaginator $resource): array
    {
        $paginated = $resource->toArray();
        $data['data'] = $paginated['data'];
        $paginationInformation = $this->formatPaginatedData($paginated);

        return array_merge_recursive($data, $paginationInformation);
    }

    protected function formatResource(JsonResource $resource): array
    {
        return array_merge_recursive(
            $resource->resolve(),
            $resource->with(),
            $resource->additional
        );
    }

    protected function formatResourceCollection(ResourceCollection $resource): array
    {
        $data = array_merge_recursive(
            $resource->resolve(),
            $resource->with(),
            $resource->additional
        );
        if ($resource->resource instanceof AbstractPaginator) {
            $paginated = $resource->resource->toArray();
            $paginationInformation = $this->formatPaginatedData($paginated);

            $data = array_merge_recursive($data, $paginationInformation);
        }

        return $data;
    }


    #[ArrayShape(['meta' => 'array', 'links' => 'array'])]
    protected function formatPaginatedData(array $paginated): array
    {
        return [
            'meta' => [
                'to' => $paginated['to'] ?? 0,
                'per_page' => $paginated['per_page'] ?? 0,
                'current_page' => $paginated['current_page'] ?? 0,
                'path' => $paginated['path'] ?? '',
                'from' => $paginated['from'] ?? 0,
            ],
            'links' => [
                'first' => $paginated['first_page_url'] ?? '',
                'last' => $paginated['last_page_url'] ?? '',
                'next' => $paginated['next_page_url'] ?? '',
                'prev' => $paginated['prev_page_url'] ?? '',
            ],
        ];
    }


    public function fail(
        int $status = BusCode::SUCCESS,
        string $message = '',
        array $errors = [],
        int $code = HttpCode::OK,
    ): PsrResponseInterface {
        if (empty($message)) {
            $message = BusCode::getMessage($status) ?? 'bus error';
        }

        if (!config('app_debug')) {
            $errors = [];
        }

        return $this->formatting($code, $message, $status, errors: $errors);
    }

    protected function formatting(
        int $code,
        string $message,
        int $status = BusCode::SUCCESS,
        $data = [],
        array $errors = []
    ): PsrResponseInterface {
        $body = [
            'request_id' => Context::get(AppendRequestProcessor::LOG_REQUEST_ID_KEY),
            'status' => $status,
            'message' => $message,
        ];

        !empty($errors) && $body['debug'] = $errors;
        if (!empty($data)) {
            $body = isset($data['data']) ? array_merge($body, $data) : array_merge($body, ['data' => $data]);
        }

        return $this->withAddedHeaders(['content-type' => 'application/json; charset=utf-8'])
            ->withStatus($code)
            ->json($body);
    }

    public function withAddedHeaders(array $headers): PsrResponseInterface
    {
        $config = config('response.headers') ?? [];
        $headers = array_merge($config, $headers);

        if (!Context::has(ResponseInterface::class)) {
            $response = di(ResponseInterface::class);
            Context::set(ResponseInterface::class, $response);
        }

        return Context::override(
            ResponseInterface::class,
            static function (ResponseInterface $response) use ($headers) {
                $newResponse = $response;
                foreach ($headers as $key => $value) {
                    $newResponse = $newResponse->withHeader($key, $value);
                }

                return $newResponse;
            }
        );
    }
}
