<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Support\Macros;

use Hyperf\Utils\Collection;

class CollectionMacro
{
    public function reduces(): callable
    {
        return function (callable $callback, $carry = null) {
            /* @var Collection $this */
            return array_reduces($this->items, $callback, $carry);
        };
    }
}
