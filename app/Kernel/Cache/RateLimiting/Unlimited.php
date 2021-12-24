<?php

declare(strict_types=1);

namespace App\Kernel\Cache\RateLimiting;

use JetBrains\PhpStorm\Pure;

class Unlimited extends GlobalLimit
{
    /**
     * Create a new limit instance.
     *
     * @return void
     */
    #[Pure]
    public function __construct()
    {
        parent::__construct(PHP_INT_MAX);
    }
}
