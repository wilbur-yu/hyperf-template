<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Kernel\Http;

use App\Exception\RequestNotFoundException;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

class RouteContext
{
    public function getRouteName(): string
    {
        $dispatched = $this->getRequest()->getAttribute(Dispatched::class);
        if (! $dispatched instanceof Dispatched) {
            throw new RequestNotFoundException('Request is invalid.');
        }

        $handler = $dispatched->handler;
        return $handler->options['name'] ?? $handler->route;
    }

    protected function getRequest(): ServerRequestInterface
    {
        $request = Context::get(ServerRequestInterface::class);
        if (! $request instanceof ServerRequestInterface) {
            throw new RequestNotFoundException('Request is not found.');
        }
        return $request;
    }
}
