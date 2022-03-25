<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Middleware;

use App\Constants\BusCode;
use App\Constants\BusConstant\GuardConstant;
use App\Exception\AuthorizationException;
use Hyperf\Utils\Context;
use HyperfExt\Auth\Middlewares\AbstractAuthenticateMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationMiddleware extends AbstractAuthenticateMiddleware
{
    protected function authenticate(ServerRequestInterface $request, array $guards): void
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                Context::set(GuardConstant::GUARD_CURRENT_KEY, $guard);
                $this->auth->shouldUse($guard);
                Context::set($guard, $this->auth->guard($guard)->user());

                return;
            }
        }

        !$this->passable() && $this->unauthenticated($request, $guards);
    }

    protected function unauthenticated(ServerRequestInterface $request, array $guards): void
    {
        throw new AuthorizationException(BusCode::SERVICE_UNAUTHORIZED, );
    }

    protected function guards(): array
    {
        return array_keys(config('auth.guards'));
    }
}
