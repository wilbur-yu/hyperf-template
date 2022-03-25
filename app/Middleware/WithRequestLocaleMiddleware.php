<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Middleware;

use Hyperf\Contract\TranslatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WithRequestLocaleMiddleware implements MiddlewareInterface
{
    private TranslatorInterface $translator;

    public function __construct()
    {
        $this->translator = di(TranslatorInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $request->getHeaderLine('locale');
        if (in_array($locale, ['en', 'zh_CN'])) {
            $this->translator->setLocale($locale);
        }

        return $handler->handle($request);
    }
}
