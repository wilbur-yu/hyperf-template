<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Middleware;

use Hyperf\Context\Context;
use Hyperf\Contract\TranslatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WithRequestInitMiddleware implements MiddlewareInterface
{
    private TranslatorInterface $translator;

    /**
     * @param  \Psr\Container\ContainerInterface  $container
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->translator = $this->container->get(TranslatorInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setLocale($request);
        $this->setScheme($request);
        $this->setHeader($request);

        return $handler->handle($request);
    }

    public function setHeader(ServerRequestInterface $request): void
    {
        $newRequest = $request->withAddedHeader('Accept', 'application/json');

        Context::set(ServerRequestInterface::class, $newRequest);
    }

    protected function setScheme(ServerRequestInterface $request): void
    {
        if ($request->getHeaderLine('https') === 'on') {
            $newUri = $request->getUri()->withScheme('https');
            $newRequest = $request->withUri($newUri);
            Context::set(ServerRequestInterface::class, $newRequest);
        }
    }

    protected function setLocale(ServerRequestInterface $request): void
    {
        $locale = $request->getHeaderLine('locale');
        if (in_array($locale, ['en', 'zh_CN'])) {
            $this->translator->setLocale($locale);
        }
    }
}
