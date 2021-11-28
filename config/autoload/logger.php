<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

$env = env('APP_ENV');
$level = match ($env) {
    'prod' => Monolog\Logger::ERROR,
    'test' => Monolog\Logger::INFO,
    default => Monolog\Logger::DEBUG,
};

return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH.'/runtime/logs/hyperf.log',
                'level' => $level,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
                'includeStacktraces' => true,
            ],
        ],
        'processors' => [
            [
                'class' => App\Kernel\Log\AppendRequestProcessor::class,
            ],
        ],
    ],
];
