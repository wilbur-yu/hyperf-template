<?php

declare(strict_types=1);

return [
    'xiZhi' => [
        'type' => env('NOTIFY_XIZHI_TYPE', 'single'), // [single, channel]
        'token' => env('NOTIFY_XIZHI_TOKEN'),
    ],
    'dingTalk' => [
        'keyword' => env('NOTIFY_DINGTALK_KEYWORD'),
        'token' => env('NOTIFY_DINGTALK_TOKEN'),
        'secret' => env('NOTIFY_DINGTALK_SECRET'),
    ],
];