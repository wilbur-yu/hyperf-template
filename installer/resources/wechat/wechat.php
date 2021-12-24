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
    /*
     * 公众号
     */
    'official_account' => [
        'default' => [
            'app_id'  => env('WECHAT_OFFICIAL_ACCOUNT_APPID', 'your-app-id'),         // AppID
            'secret'  => env('WECHAT_OFFICIAL_ACCOUNT_SECRET', 'your-app-secret'),    // AppSecret
            'token'   => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN', 'your-token'),           // Token
            'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY', ''),                 // EncodingAESKey

            /*
             * OAuth 配置
             *
             * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
             * callback：OAuth授权完成后的回调页地址(如果使用中间件，则随便填写。。。)
             * enforce_https：是否强制使用 HTTPS 跳转
             */
            // 'oauth'   => [
            //     'scopes'        => array_map('trim', explode(',', env('WECHAT_OFFICIAL_ACCOUNT_OAUTH_SCOPES', 'snsapi_userinfo'))),
            //     'callback'      => env('WECHAT_OFFICIAL_ACCOUNT_OAUTH_CALLBACK', '/examples/oauth_callback.php'),
            //     'enforce_https' => true,
            // ],
        ],
    ],

    /*
     * 小程序
     */
    'mini_program' => [
        'default' => [
            'app_id'  => env('WECHAT_MINI_PROGRAM_APPID', 'wx9e21c4ff95b46d2f'),
            'secret'  => env('WECHAT_MINI_PROGRAM_SECRET', ''),
            'token'   => env('WECHAT_MINI_PROGRAM_TOKEN', ''),
            'aes_key' => env('WECHAT_MINI_PROGRAM_AES_KEY', ''),
        ],
    ],

    /*
     * 微信支付
     */
    'payment' => [
        'default' => [
            'sandbox'    => env('WECHAT_PAYMENT_SANDBOX', false),
            'app_id'     => env('WECHAT_PAYMENT_APPID', ''),
            'mch_id'     => env('WECHAT_PAYMENT_MCH_ID', 'your-mch-id'),
            'key'        => env('WECHAT_PAYMENT_KEY', 'key-for-signature'),
            'cert_path'  => env('WECHAT_PAYMENT_CERT_PATH', 'path/to/cert/apiclient_cert.pem'),    // XXX: 绝对路径！！！！
            'key_path'   => env('WECHAT_PAYMENT_KEY_PATH', 'path/to/cert/apiclient_key.pem'),      // XXX: 绝对路径！！！！
            'notify_url' => 'http://example.com/payments/wechat-notify',                           // 默认支付结果通知地址
        ],
        // ...
    ],
];
