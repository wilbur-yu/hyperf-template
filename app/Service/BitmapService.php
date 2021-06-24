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
namespace App\Service;

use Carbon\Carbon;
use Hyperf\Redis\Redis;
use function config;

class BitmapService
{
    protected Redis $redis;

    protected string $key = '';

    protected string $lastKey = '';

    /**
     * Bitmap constructor.
     *
     * @param string $keyPrefix
     * @param string $keyCarbonFormat key 格式
     */
    public function __construct(string $keyPrefix = 'user:signing', string $keyCarbonFormat = '')
    {
        $this->redis = redis();
        $this->key   = config('cache.default.prefix') . ':' . $keyPrefix .
                       (empty($keyCarbonFormat) ? '' : ':' . Carbon::now()->format($keyCarbonFormat));
    }

    public function resetKey(): self
    {
        $tmp           = $this->key;
        $this->key     = $this->lastKey;
        $this->lastKey = $tmp;

        return $this;
    }

    public function add(int $id): int
    {
        return $this->redis->setBit($this->key, $id, true);
    }

    public function has(int $id): bool
    {
        return (bool) $this->redis->getBit($this->key, $id);
    }

    public function sub(int $id): int
    {
        return $this->redis->setBit($this->key, $id, false);
    }
}
