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
return [
    'enabled'    => env('SOAR_ENABLED', env('APP_ENV') === 'dev'),
    '-soar-path' => env('SOAR_PATH', ''), // soar 二进制执行文件存储目录
    /*
     * 测试来环境连接参数
     */
    '-test-dsn' => [
        'host'     => env('SOAR_TEST_DSN_HOST', '127.0.0.1'),
        'port'     => env('SOAR_TEST_DSN_PORT', '3306'),
        'dbname'   => env('SOAR_TEST_DSN_DBNAME', 'database'),
        'username' => env('SOAR_TEST_DSN_USER', 'root'),
        'password' => env('SOAR_TEST_DSN_PASSWORD', ''),
        'disable'  => env('SOAR_TEST_DSN_DISABLE', false),
    ],
    '-sampling'            => env('SOAR_SAMPLING', true), // 是否开启数据采样开关
    '-allow-drop-index'    => env('SOAR_ALLOW_DROP_INDEX', true), // 允许输出删除重复索引的建议
    '-drop-test-temporary' => env('SOAR_DROP_TEST_TEMPORARY', true), // 是否清理测试环境产生的临时库表
    '-log-output'          => BASE_PATH . '/runtime/logs/soar.log',
];
