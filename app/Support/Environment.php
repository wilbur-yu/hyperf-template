<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu
 */

namespace App\Support;

use Hyperf\Macroable\Macroable;
use Hyperf\Utils\Str;
use InvalidArgumentException;

class Environment
{
    use Macroable;

    protected string $env;

    public function __construct()
    {
        $this->env = config('app_env');
    }

    /**
     * Get or check the current application environment.
     *
     * @param  array|string  $environments
     *
     * @return bool
     */
    public function is(...$environments): bool
    {
        count($environments) < 0 && throw new InvalidArgumentException();
        $patterns = is_array($environments[0]) ? $environments[0] : $environments;

        return Str::is($patterns, $this->env);
    }

    /**
     * Determine if the application is in the local environment.
     */
    public function isLocal(): bool
    {
        return $this->env === 'local';
    }

    /**
     * Determine if the application is in the dev environment.
     */
    public function isDev(): bool
    {
        return $this->env === 'dev';
    }

    public function isTest(): bool
    {
        return $this->env === 'test';
    }

    /**
     * Determine if the application is in the develop environment.
     */
    public function isDevelop(): bool
    {
        return $this->env === 'develop';
    }

    /**
     * Determine if the application is in the production environment.
     */
    public function isProduction(): bool
    {
        return $this->env === 'production';
    }

    public function isNotProduction(): bool
    {
        return !$this->isProduction();
    }

    /**
     * Determine if the application is in the production environment.
     */
    public function isOnline(): bool
    {
        return $this->env === 'online';
    }
}
