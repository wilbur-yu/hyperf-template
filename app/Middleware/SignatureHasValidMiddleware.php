<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Middleware;

use App\Exception\SignatureException;
use App\Support\Signed;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SignatureHasValidMiddleware implements MiddlewareInterface
{
    protected Signed $signed;

    /**
     * @param  \Psr\Container\ContainerInterface  $container
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->signed = $this->container->get(Signed::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = $request->getParsedBody();
        $query = $request->getQueryParams();
        if ($this->signed->hasValid(array_merge($body, $query))) {
            return $handler->handle($request);
        }

        throw new SignatureException(403, 'Invalid signature');
    }
}
