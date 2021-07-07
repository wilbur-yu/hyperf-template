<?php

declare(strict_types = 1);
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
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WechatFactory.
 *
 * @method \EasyWeChat\Payment\Application            payment(?string $name = null)
 * @method  \EasyWeChat\MiniProgram\Application        miniProgram(?string $name = null)
 * @method \EasyWeChat\OpenPlatform\Application       openPlatform(?string $name = null)
 * @method \EasyWeChat\OfficialAccount\Application    officialAccount(?string $name = null)
 * @method \EasyWeChat\BasicService\Application       basicService(?string $name = null)
 * @method \EasyWeChat\Work\Application               work(?string $name = null)
 * @method \EasyWeChat\OpenWork\Application           openWork(?string $name = null)
 * @method Application      microMerchant()
 */
class WechatFactory
{
    /**
     * @Value("wechat")
     */
    protected array $config;

    /**
     * @Inject
     */
    protected ContainerInterface $container;

    public function __call($name, $arguments): ServiceContainer
    {
        return $this->make($name, ...$arguments);
    }

    public function make(string $service, $name = null): ServiceContainer
    {
        $service = Str::snake($service);
        $config  = $name === null ? $this->config[$service]['default'] : $this->config[$service][$name];

        $app = Factory::make($service, $config);

        $handler = new CoroutineHandler();

        // 设置 HttpClient，部分接口直接使用了 http_client。
        $config            = $app['config']->get('http', []);
        $config['handler'] = $stack = HandlerStack::create($handler);
        $app->rebind('http_client', new Client($config));

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $app['guzzle_handler'] = $handler;

        // 替换 cache
        $app['cache'] = $this->container->get(CacheInterface::class);

        // 如果使用的是 OfficialAccount，则还需要设置以下参数
        if ($app instanceof \EasyWeChat\OfficialAccount\Application) {
            $app->oauth->setGuzzleOptions([
                'http_errors' => false,
                'handler'     => $stack,
            ]);
        }

        $get         = $this->request->getQueryParams();
        $post        = $this->request->getParsedBody();
        $cookie      = $this->request->getCookieParams();
        $uploadFiles = $this->request->getUploadedFiles() ?? [];
        $server      = $this->request->getServerParams();
        $xml         = $this->request->getBody()->getContents();
        $files       = [];
        /** @var \Hyperf\HttpMessage\Upload\UploadedFile $v */
        foreach ($uploadFiles as $k => $v) {
            $files[$k] = $v->toArray();
        }
        $request          = new Request(
            $get,
            $post,
            [],
            $cookie,
            $files,
            $server,
            $xml
        );
        $request->headers = new HeaderBag($this->request->getHeaders());
        $app->rebind('request', $request);

        $app->rebind('cache', cache());

        return $app;
    }
}
