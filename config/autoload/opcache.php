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
return [
    'directories' => [
        BASE_PATH . '/app',
        BASE_PATH . '/bin',
        BASE_PATH . '/config',
        BASE_PATH . '/vendor',
        BASE_PATH . '/runtime/container',
        BASE_PATH . '/storage',
    ],
    'exclude_dirs' => [
        'test',
        'Test',
        'tests',
        'Tests',
        'stub',
        'Stub',
        'stubs',
        'Stubs',
        'dumper',
        'Dumper',
        'Autoload',
        'swoole',
        'jetbrains',
        'symfony/polyfill-intl-idn',
    ],

    'exclude_files' => [
        'bootstrap80.php',
    ],
];
