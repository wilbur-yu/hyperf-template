<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Auth\Driver;

use Hyperf\Cache\Annotation\CachePut;
use HyperfExt\Auth\Contracts\AuthenticatableInterface;
use HyperfExt\Auth\UserProviders\ModelUserProvider;

class ModelUserCacheProvider extends ModelUserProvider
{
    #[CachePut]
    public function retrieveById($identifier): ?AuthenticatableInterface
    {
        return parent::retrieveById($identifier);
    }

    #[CachePut]
    public function retrieveByToken($identifier, string $token): ?AuthenticatableInterface
    {
        return parent::retrieveByToken($identifier, $token);
    }

    #[CachePut]
    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface
    {
        return parent::retrieveByCredentials($credentials);
    }
}
