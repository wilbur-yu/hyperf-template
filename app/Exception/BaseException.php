<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Exception;

use App\Constants\BusCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class BaseException extends ServerException
{
    protected array $data;

    public function __construct(int $code = 0, string $message = null, Throwable $previous = null, array $data = [])
    {
        if (is_null($message)) {
            $message = BusCode::getMessage($code);
        }

        $this->data = $data;

        parent::__construct($message, $code, $previous);
    }

    public function getData(): array
    {
        return $this->data;
    }
}
