<?php

declare(strict_types = 1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Driver\Cache;

use Hyperf\Cache\Driver\RedisDriver as BaseRedisDriver;

class RedisDriver extends BaseRedisDriver
{
    public function increment(string $key, int $value = 1): int
    {
        return $this->redis->incrBy($this->getCacheKey($key), $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->redis->decrBy($this->getCacheKey($key), $value);
    }
}
