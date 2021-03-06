<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Kernel\Contract;

use App\Constants\HttpCode;
use Hyperf\HttpServer\Contract\ResponseInterface as BaseResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface extends BaseResponseInterface, PsrResponseInterface
{
    public function success(
        mixed $data = [],
        string $message = 'success',
        int $code = HttpCode::OK
    ): PsrResponseInterface;

    public function fail(
        int $status = HttpCode::OK,
        string $message = '',
        array $errors = [],
        int $code = HttpCode::OK,
    ): PsrResponseInterface;

    public function addedHeaders(array $headers): PsrResponseInterface;
}
