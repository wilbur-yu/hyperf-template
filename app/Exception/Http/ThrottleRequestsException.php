<?php

declare(strict_types=1);

namespace App\Exception\Http;

use App\Constants\HttpCode;
use Hyperf\Server\Exception\ServerException;

class ThrottleRequestsException extends ServerException
{
    public function __construct(int $code, ?string $message = null, protected array $headers = [])
    {
        if (is_null($message)) {
            $message = HttpCode::getMessage($code);
        }
        parent::__construct($message, $code);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
