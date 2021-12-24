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
use Hyperf\Session\Handler\RedisHandler;

return [
    'handler' => RedisHandler::class,
    'options' => [
        'connection'      => 'default',
        'path'            => BASE_PATH . '/runtime/sessions',
        'gc_maxlifetime'  => 24 * 60 * 60 * 10,
        'session_name'    => 'WEY_ACTIVITY_SESSION_ID',
        'domain'          => null,
        'cookie_lifetime' => 24 * 60 * 60 * 20,
        'expire_on_close' => false,
    ],
];
