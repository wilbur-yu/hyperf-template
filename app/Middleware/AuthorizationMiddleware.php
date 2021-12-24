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
use App\Constants\GuardConstant;
use App\Exception\AuthorizationException;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use HyperfExt\Auth\Middlewares\AbstractAuthenticateMiddleware;

class AuthorizationMiddleware extends AbstractAuthenticateMiddleware //implements MiddlewareInterface //
{
    // protected ContainerInterface $container;
    //
    // protected Jwt $jwt;
    //
    // protected Manager $jwtManager;

    // public function __construct(ContainerInterface $container, JwtFactoryInterface $jwtFactory)
    // {
    //     $this->container = $container;
    //     $this->jwt = $jwtFactory->make();
    //     $this->jwtManager = $this->jwt->getManager();
    // }
    //
    // public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    // {
    //     try {
    //         $this->jwt->parseToken()->checkOrFail();
    //         $guard = $this->jwt->getClaim('guard');
    //         Context::set(GuardConstant::GUARD_CURRENT_KEY, $guard);
    //         Context::set($guard, auth($guard)->user());
    //     } catch (Throwable $e) {
    //         throw new AuthorizationException(BusCode::SERVICE_UNAUTHORIZED, $e->getMessage());
    //     }
    //
    //     return $handler->handle($request);
    // }

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
