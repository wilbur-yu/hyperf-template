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
$timestamp = date('Y_m_d_His');

return [
    'packages' => [
        'hyperf/amqp' => [
            'version' => '~2.2.0',
        ],
        'hyperf/async-queue' => [
            'version' => '~2.2.0',
        ],
        'hyperf/database' => [
            'version' => '~2.2.0',
        ],
        'hyperf/db-connection' => [
            'version' => '~2.2.0',
        ],
        'hyperf/model-cache' => [
            'version' => '~2.2.0',
        ],
        'hyperf/constants' => [
            'version' => '~2.2.0',
        ],
        'hyperf/json-rpc' => [
            'version' => '~2.2.0',
        ],
        'hyperf/redis' => [
            'version' => '~2.2.0',
        ],
        'hyperf/rpc' => [
            'version' => '~2.2.0',
        ],
        'hyperf/rpc-client' => [
            'version' => '~2.2.0',
        ],
        'hyperf/rpc-server' => [
            'version' => '~2.2.0',
        ],
        'hyperf/grpc-client' => [
            'version' => '~2.2.0',
        ],
        'hyperf/grpc-server' => [
            'version' => '~2.2.0',
        ],
        'hyperf/elasticsearch' => [
            'version' => '~2.2.0',
        ],
        'hyperf/config-apollo' => [
            'version' => '~2.2.0',
        ],
        'hyperf/config-aliyun-acm' => [
            'version' => '~2.2.0',
        ],
        'hyperf/config-etcd' => [
            'version' => '~2.2.0',
        ],
        'hyperf/tracer' => [
            'version' => '~2.2.0',
        ],
        'hyperf/service-governance' => [
            'version' => '~2.2.0',
        ],
        'hyperf/validation' => [
            'version' => '~2.2.0',
        ],
        'hyperf/session' => [
            'version' => '~2.2.0',
        ],
        'hyperf/view' => [
            'version' => '~2.2.0',
        ],
        'hyperf/view-engine' => [
            'version' => '~2.2.0',
        ],
        'hyperf/task' => [
            'version' => '~2.2.0',
        ],
        'hyperf/resource' => [
            'version' => '~2.2.0',
        ],
        'overtrue/wechat' => [
            'version' => '^5.7',
        ],
        'wilbur-yu/hyperf-options' => [
            'version' => '^0.1',
        ],
        'hyperf-ext/encryption' => [
            'version' => '^2.1',
        ],
        'hyperf-ext/hashing' => [
            'version' => '^2.1',
        ],
        'hyperf-ext/auth' => [
            'version' => '^2.1',
        ],
    ],
    'require-dev' => [
    ],
    'questions' => [
        'database' => [
            'question' => 'Do you want to use Database (MySQL Client) ?',
            'default' => 'y',
            'required' => false,
            'force' => false,
            'custom-package' => true,
            'options' => [
                'y' => [
                    'name' => 'yes',
                    'packages' => [
                        'hyperf/database',
                        'hyperf/db-connection',
                    ],
                    'resources' => [
                        'resources/database/databases.php' => 'config/autoload/databases.php',
                    ],
                    'commands' => [],
                ],
            ],
        ],
        'options' => [
            'question' => 'Do you want to use wilbur-yu/options component ?',
            'default' => 'y',
            'required' => false,
            'force' => false,
            'custom-package' => true,
            'options' => [
                'y' => [
                    'name' => 'yes',
                    'packages' => [
                        'wilbur-yu/hyperf-options',
                    ],
                    'resources' => [
                        'resources/options/options.php' => 'config/autoload/options.php',
                        'resources/options/create_options_table.php' => "migrations/{$timestamp}_create_options_table.php",
                    ],
                ],
            ],
        ],
        'async-queue' => [
            'question' => 'Do you want to use hyperf/async-queue component ? (A simple redis queue component)',
            'default' => 'n',
            'required' => false,
            'force' => true,
            'custom-package' => true,
            'options' => [
                'y' => [
                    'name' => 'yes',
                    'packages' => [
                        'hyperf/async-queue',
                    ],
                    'resources' => [
                        'resources/async_queue/async_queue.php' => 'config/autoload/async_queue.php',
                        'resources/async_queue/AsyncQueueConsumer.php' => 'app/Process/AsyncQueueConsumer.php',
                        'resources/async_queue/QueueHandleListener.php' => 'app/Listener/QueueHandleListener.php',
                    ],
                ],
            ],
        ],
        'model-cache' => [
            'question' => 'Do you want to use hyperf/model-cache component ?',
            'default' => 'n',
            'required' => false,
            'force' => true,
            'custom-package' => true,
            'options' => [
                'y' => [
                    'name' => 'yes',
                    'packages' => [
                        'hyperf/model-cache',
                    ],
                    'resources' => [
                        'resources/model_cache/Model.php' => 'app/Model/Model.php',
                        'resources/model_cache/databases.php' => 'config/autoload/databases.php',
                    ],
                ],
            ],
        ],
        'view' => [
            'question' => 'Do you want to use hyperf/view component ?',
            'default' => 'n',
            'required' => false,
            'force' => true,
            'custom-package' => true,
            'options' => [
                'y' => [
                    'name' => 'yes',
                    'packages' => [
                        'hyperf/view',
                        'hyperf/view-engine',
                        'hyperf/task',
                    ],
                    'resources' => [
                        'resources/view/view.php' => 'config/autoload/view.php',
                        'resources/view/storage/views/' => 'storage/views/',
                        'resources/task/server.php' => 'config/autoload/server.php',
                    ],
                ],
            ],
        ],
        'session' => [
            'question' => 'Do you want to use hyperf/session component ?',
            'default' => 'n',
            'required' => false,
            'force' => false,
            'custom-package' => true,
            'options' => [
                'y' => [
                    'name' => 'yes',
                    'packages' => [
                        'hyperf/session',
                    ],
                    'resources' => [
                        'resources/session/session.php' => 'config/autoload/session.php',
                        'resources/session/SessionMiddleware.php' => 'app/Middleware/SessionMiddleware.php',
                    ],
                ],
            ],
        ],
        'wechat' => [
            'question' => 'Do you want to use overtrue/wechat component ?',
            'default' => 'n',
            'required' => false,
            'force' => false,
            'custom-package' => true,
            'options' => [
                'y' => [
                    'name' => 'yes',
                    'packages' => [
                        'overtrue/wechat',
                    ],
                    'resources' => [
                        'resources/wechat/wechat.php' => 'config/autoload/wechat.php',
                        'resources/wechat/Factory/WechatFactory.php' => 'app/Factory/WechatFactory.php',
                    ],
                ],
            ],
        ],
        'auth' => [
            'question' => 'Do you want to use hyperf-ext/auth component ?',
            'default' => 'n',
            'required' => false,
            'force' => false,
            'custom-package' => true,
            'options' => [
                'y' => [
                    'name' => 'yes',
                    'packages' => [
                        'hyperf-ext/auth',
                    ],
                    'resources' => [
                        'resources/auth/auth.php' => 'config/autoload/auth.php',
                        'resources/auth/jwt.php' => 'config/autoload/jwt.php',
                        'resources/auth/Auth/Driver/ModelUserCacheProvider.php' => 'app/Auth',
                        'resources/auth/Auth/Guard/SsoGuard.php' => 'app/Auth',
                    ],
                ],
            ],
        ],
    ],
];
