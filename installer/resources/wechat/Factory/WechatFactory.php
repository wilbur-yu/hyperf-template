<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Factory;

use EasyWeChat\Factory;
use EasyWeChat\Kernel\ServiceContainer;
use EasyWeChat\Kernel\Support\Str;
use EasyWeChat\MicroMerchant\Application;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WechatFactory.
 *
 * @method \EasyWeChat\Payment\Application         payment(?string $name = null, ...$options)
 * @method \EasyWeChat\MiniProgram\Application     miniProgram(?string $name = null, ...$options)
 * @method \EasyWeChat\OpenPlatform\Application    openPlatform(?string $name = null, ...$options)
 * @method \EasyWeChat\OfficialAccount\Application officialAccount(?string $name = null, ...$options)
 * @method \EasyWeChat\BasicService\Application    basicService(?string $name = null, ...$options)
 * @method \EasyWeChat\Work\Application            work(?string $name = null, ...$options)
 * @method \EasyWeChat\OpenWork\Application        openWork(?string $name = null, ...$options)
 * @method Application                             microMerchant()
 */
class WechatFactory
{
    #[Value('wechat')]
    protected array $config;

    #[Inject]
    protected ContainerInterface $container;

    public function __call($name, $arguments): ServiceContainer
    {
        return $this->make($name, ...$arguments);
    }

    public function make(string $service, $name = null, bool $isRebindRequest = false): ServiceContainer
    {
        $service = Str::snake($service);
        $config = null === $name ? $this->config[$service]['default'] : $this->config[$service][$name];

        $app = Factory::make($service, $config);

        if ($isRebindRequest) {
            $app = $this->rebindRequest($app);
        }

        $handler = new CoroutineHandler();

        // 设置 HttpClient，部分接口直接使用了 http_client。
        $config = $app['config']->get('http', []);
        $config['handler'] = $stack = HandlerStack::create($handler);
        $app->rebind('http_client', new Client($config));

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $app['guzzle_handler'] = $handler;

        // 替换 cache
        $app['cache'] = di(CacheInterface::class);

        // 如果使用的是 OfficialAccount，则还需要设置以下参数
        if ($app instanceof \EasyWeChat\OfficialAccount\Application) {
            $app->oauth->setGuzzleOptions([
                'http_errors' => false,
                'handler' => $stack,
            ]);
        }

        $app->rebind('cache', cache());

        return $app;
    }

    protected function rebindRequest(ServiceContainer $app): ServiceContainer
    {
        $request = Context::get(RequestInterface::class);

        $get = $request->getQueryParams();
        $post = $request->getParsedBody();
        $cookie = $request->getCookieParams();
        $uploadFiles = $request->getUploadedFiles() ?? [];
        $server = $request->getServerParams();
        $xml = $request->getBody()->getContents();
        $files = [];
        /** @var \Hyperf\HttpMessage\Upload\UploadedFile $v */
        foreach ($uploadFiles as $k => $v) {
            $files[$k] = $v->toArray();
        }
        $newRequest = new Request($get, $post, [], $cookie, $files, $server, $xml);
        $newRequest->headers = new HeaderBag($request->getHeaders());
        $app->rebind('request', $newRequest);

        return $app;
    }
}
