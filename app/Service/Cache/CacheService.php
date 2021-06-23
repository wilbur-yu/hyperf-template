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
namespace App\Service\Cache;

use App\Contract\CacheInterface;
use Hyperf\Cache\Cache;

class CacheService extends Cache implements CacheInterface
{
    public function increment(string $key, int $value = 1)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function decrement(string $key, int $value = 1)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
