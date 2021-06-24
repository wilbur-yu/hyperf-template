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
namespace App\Kernel\Contract;

use App\Constants\HttpCode;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface
{
    public function success(
        array $data = [],
        string $message = 'success',
        int $code = HttpCode::HTTP_OK
    ): PsrResponseInterface;

    public function fail(int $code, string $message = '', array $errors = []): PsrResponseInterface;
}
