<?php

declare(strict_types=1);
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

namespace App\Middleware;

use Carbon\Carbon;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\Session\SessionManager;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @var SessionManager
     */
    private SessionManager $sessionManager;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    public function __construct(SessionManager $sessionManager, ConfigInterface $config)
    {
        $this->sessionManager = $sessionManager;
        $this->config         = $config;
    }

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->isSessionAvailable()) {
            return $handler->handle($request);
        }

        $session = $this->sessionManager->start($request);

        try {
            $response = $handler->handle($request);
        } finally {
            $this->storeCurrentUrl($request, $session);
            $this->sessionManager->end($session);
        }

        return $this->addCookieToResponse($request, $response, $session);
    }

    private function isSessionAvailable(): bool
    {
        return $this->config->has('session.handler');
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Hyperf\Contract\SessionInterface  $session
     */
    private function storeCurrentUrl(RequestInterface $request, SessionInterface $session): void
    {
        if ($request->getMethod() === 'GET') {
            $session->setPreviousUrl($this->fullUrl($request));
        }
    }

    /**
     * Get the full URL for the request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return string
     */
    private function fullUrl(RequestInterface $request): string
    {
        $uri      = $request->getUri();
        $query    = $uri->getQuery();
        $question = $uri->getHost() . $uri->getPath() === '/' ? '/?' : '?';

        return $query ? $this->url($request) . $question . $query : $this->url($request);
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return string
     */
    private function url(RequestInterface $request): string
    {
        return rtrim(preg_replace('/\?.*/', '', (string) $request->getUri()));
    }

    /**
     * Add the session cookie to the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param \Hyperf\Contract\SessionInterface        $session
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function addCookieToResponse(
        ServerRequestInterface $request,
        ResponseInterface $response,
        SessionInterface $session
    ): ResponseInterface {
        $uri      = $request->getUri();
        $path     = '/';
        $secure   = strtolower($uri->getScheme()) === 'https';
        $httpOnly = true;

        $domain = $this->config->get('session.options.domain') ?? $uri->getHost();

        $cookie = new Cookie(
            $session->getName(),
            $session->getId(),
            $this->getCookieExpirationDate(),
            $path,
            $domain,
            $secure,
            $httpOnly,
            false,
            Cookie::SAMESITE_LAX
        );
        if (! method_exists($response, 'withCookie')) {
            return $response->withHeader('Set-Cookie', (string) $cookie);
        }

        /* @var \Hyperf\HttpMessage\Server\Response $response */
        return $response->withCookie($cookie);
    }

    /**
     * Get the session lifetime in seconds.
     */
    private function getCookieExpirationDate(): int
    {
        if ($this->config->get('session.options.expire_on_close')) {
            $expirationDate = 0;
        } else {
            $expireSeconds  = $this->config->get('session.options.cookie_lifetime', 5 * 60 * 60);
            $expirationDate = Carbon::now()->addSeconds($expireSeconds)->getTimestamp();
        }

        return $expirationDate;
    }
}
