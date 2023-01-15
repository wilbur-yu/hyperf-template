<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu
 */

namespace App\Exception;

use App\Exception\BaseException;

class DecryptException extends BaseException
{
    public function getTitle(): string
    {
        return '加解密服务异常';
    }
}
