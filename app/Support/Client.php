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
namespace App\Support;

use Hyperf\Guzzle\HandlerStackFactory;

class Client
{
    public function make(array $config = [])
    {
        $factory = new HandlerStackFactory();
        $stack   = $factory->create();
        $config  = array_merge([
            'handler' => $stack,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => 2.0,
        ], $config);

        return make(\GuzzleHttp\Client::class, [$config]);
    }
}
