<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Auth\Guard;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\Redis;
use HyperfExt\Auth\Contracts\AuthenticatableInterface;
use HyperfExt\Auth\Contracts\UserProviderInterface;
use HyperfExt\Auth\Guards\JwtGuard;
use HyperfExt\Jwt\JwtFactory;
use HyperfExt\Jwt\Token;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class SsoGuard extends JwtGuard
{
    protected Redis $redis;

    public function __construct(
        ContainerInterface $container,
        RequestInterface $request,
        JwtFactory $jwtFactory,
        EventDispatcherInterface $eventDispatcher,
        UserProviderInterface $provider,
        string $name
    ) {
        $this->container = $container;
        $this->request = $request;
        $this->jwt = $jwtFactory->make();
        $this->eventDispatcher = $eventDispatcher;
        $this->provider = $provider;
        $this->name = $name;

        parent::__construct($container, $request, $jwtFactory, $eventDispatcher, $provider, $name);
        $this->redis = make(Redis::class);
    }

    /**
     * @param  \HyperfExt\Auth\Contracts\AuthenticatableInterface  $user
     *
     * @throws \HyperfExt\Jwt\Exceptions\TokenBlacklistedException
     * @return string
     */
    public function login(AuthenticatableInterface $user): string
    {
        $client = 'mini_program';
        $token = parent::login($user);
        $redisKey = str_replace('{uid}', (string)$user->getAuthIdentifier(), 'u.token.{uid}');

        if (!empty($previousToken = $this->redis->hGet($redisKey, $client)) && $previousToken !== $token) {
            // 如果存在上一个 token，就给他拉黑，也就是强制下线
            $this->jwt->getBlacklist()->add(
                $this->jwt->getManager()->decode(
                    new Token($previousToken),
                    false,
                    true,
                )
            );
            // $this->eventDispatcher->dispatch(new ForcedOfflineEvent($user, $client));
        }
        $cachePrefix = config('cache.default.prefix').'jwt.sso.';
        $this->redis->hSet($cachePrefix.$redisKey, $client, $token);

        return $token;
    }
}
