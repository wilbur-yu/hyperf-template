<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Support\Trait;

use App\Constants\BusCode;
use App\Constants\GuardConstant;
use App\Exception\AuthorizationException;
use Hyperf\Utils\Context;

trait UserTrait
{
    protected static function user(?string $guard = null)
    {
        $guard = $guard ?? self::getCurrentGuard();
        $user = Context::get($guard);

        $user === null && throw new AuthorizationException(BusCode::SERVICE_AUTHENTICATION_TOKEN_INVALID);

        return $user;
    }

    protected static function isLogin(?string $guard = null): bool
    {
        $guard = $guard ?? self::getCurrentGuard();

        return auth($guard)->check();
    }

    protected static function getCurrentGuard()
    {
        return Context::get(GuardConstant::GUARD_CURRENT_KEY);
    }
}
