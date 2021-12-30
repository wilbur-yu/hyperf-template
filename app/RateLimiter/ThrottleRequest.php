<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\RateLimiter;

use App\Support\Trait\UserTrait;
use Psr\Http\Message\RequestInterface;

class ThrottleRequest
{
    use UserTrait;

    public static function key(): callable
    {
        return static function (RequestInterface $request) {
            if (self::isLogin()) {
                $key = sha1((string)self::user()->getAuthIdentifier());
            } else {
                $key = sha1($request->fullUrl().'|'.get_client_ip());
            }

            return $key;
        };
    }
}
