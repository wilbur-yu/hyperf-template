<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Support;

use App\Exception\SignatureException;
use Carbon\Carbon;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\InteractsWithTime;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * @license https://github.com/laravel/framework/blob/9.x/LICENSE.md
 */
class Signed
{
    use InteractsWithTime;

    protected ConfigInterface $config;

    protected const SIGNATURE_FIELD = 'signature';

    protected const EXPIRES_FIELD = 'expires';

    protected const EXPIRES_TIMEOUT_SECOND = 60;

    /**
     * @param  \Psr\Container\ContainerInterface  $container
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function forever(array $parameters = []): array
    {
        return $this->foundation($parameters);
    }

    /**
     * Create a temporary signed route URL for a named route.
     *
     * @param  array  $parameters
     * @param         $expiration
     *
     * @return array
     */
    public function temporary(array $parameters = [], $expiration = null): array
    {
        return $this->foundation($parameters, $expiration);
    }

    /**
     * Create a signed route URL for a named route.
     *
     * @param  array  $parameters
     * @param         $expiration
     *
     * @return array
     */
    protected function foundation(array $parameters = [], $expiration = null): array
    {
        $this->ensureFoundationParametersAreNotReserved($parameters);

        if ($expiration) {
            $parameters += [self::EXPIRES_FIELD => $this->availableAt($expiration)];
        }

        ksort($parameters);

        $result = [
            self::SIGNATURE_FIELD => hash_hmac('sha256', Json::encode($parameters), $this->config->get('app_key')),
        ];

        isset($parameters[self::EXPIRES_FIELD]) && $result += [self::EXPIRES_FIELD => $parameters[self::EXPIRES_FIELD]];

        return $result;
    }

    /**
     * Ensure the given signed route parameters are not reserved.
     *
     * @param  mixed  $parameters
     *
     * @return void
     */
    protected function ensureFoundationParametersAreNotReserved(array $parameters): void
    {
        if (array_key_exists(self::SIGNATURE_FIELD, $parameters)) {
            throw new InvalidArgumentException(
                '"Signature" is a reserved parameter when generating signed. Please rename your parameter.'
            );
        }

        if (array_key_exists(self::EXPIRES_FIELD, $parameters)) {
            throw new InvalidArgumentException(
                '"Expires" is a reserved parameter when generating signed. Please rename your parameter.'
            );
        }
    }

    /**
     * Determine if the given request has a valid signature.
     *
     * @param  array  $parameters
     * @param  array  $ignore
     * @param  bool   $isAllowTimeout
     *
     * @return bool
     */
    public function hasValid(array $parameters = [], array $ignore = [], bool $isAllowTimeout = true): bool
    {
        return $this->hasCorrect($parameters, $ignore)
               && $this->hasNotExpired($parameters[self::EXPIRES_FIELD] ?? null, $isAllowTimeout);
    }

    public function hasCorrect(array $parameters = [], array $ignore = []): bool
    {
        !isset($parameters[self::SIGNATURE_FIELD])
        && throw new SignatureException(
            403,
            'Signature parameter is missing, please check'
        );

        $ignore[] = self::SIGNATURE_FIELD;

        ksort($parameters);

        $original = array_diff_key($parameters, array_flip($ignore));

        $signature = hash_hmac('sha256', Json::encode($original), $this->config->get('app_key'));

        return hash_equals($signature, (string)$parameters[self::SIGNATURE_FIELD]);
    }

    /**
     * Determine if the expires timestamp from the given request is not from the past.
     *
     * @param        $expires
     * @param  bool  $isAllowTimeout
     *
     * @return bool
     */
    public function hasNotExpired($expires, bool $isAllowTimeout = true): bool
    {
        $now = Carbon::now();
        if (empty($expires)) {
            return true;
        }
        if ($isAllowTimeout
            && $now->diffInRealSeconds(Carbon::createFromTimestamp($expires))
               <= self::EXPIRES_TIMEOUT_SECOND) {
            return true;
        }

        return $now->getTimestamp() < $expires;
    }
}
