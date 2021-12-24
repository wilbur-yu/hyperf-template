<?php

declare(strict_types=1);

namespace App\Kernel\Cache\RateLimiting;

use JetBrains\PhpStorm\Pure;

class GlobalLimit extends Limit
{
    /**
     * Create a new limit instance.
     *
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return void
     */
    #[Pure]
    public function __construct(int $maxAttempts, int $decayMinutes = 1)
    {
        parent::__construct('', $maxAttempts, $decayMinutes);
    }
}
