<?php

declare(strict_types = 1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Kernel\Contract\ResponseInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    protected RequestInterface  $request;
    protected ResponseInterface $response;

    public function __construct(protected ContainerInterface $container)
    {
        $this->response = $container->get(ResponseInterface::class);
        $this->request  = $container->get(RequestInterface::class);
    }
}
