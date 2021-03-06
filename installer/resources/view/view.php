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
use Hyperf\View\Mode;
use Hyperf\ViewEngine\HyperfViewEngine;

return [
    'engine'     => HyperfViewEngine::class,
    'mode'       => Mode::TASK,
    'config'     => [
        'view_path'  => BASE_PATH . '/storage/views/',
        'cache_path' => BASE_PATH . '/runtime/views/',
    ],
    # 自定义组件注册
    'components' => [
        // 'alert' => \App\View\Components\Alert::class
    ],

    # 视图命名空间 (主要用于扩展包中)
    'namespaces' => [
        // 'admin' => BASE_PATH . '/storage/view/vendor/admin',
    ],
];
