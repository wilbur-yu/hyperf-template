<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"ALL"})
 */
#[Attribute]
class WithoutMiddlewares extends AbstractAnnotation
{
    public array $middlewares = [];

    public function __construct(...$value)
    {
        $this->middlewares = $value;
    }
}
