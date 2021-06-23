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
$timestamp = date('Y_m_d_His');

return [
    'packages'    => [
        'hyperf/amqp'               => [
            'version' => '~2.1.0',
        ],
        'hyperf/async-queue'        => [
            'version' => '~2.1.0',
        ],
        'hyperf/database'           => [
            'version' => '~2.1.0',
        ],
        'hyperf/db-connection'      => [
            'version' => '~2.1.0',
        ],
        'hyperf/model-cache'        => [
            'version' => '~2.1.0',
        ],
        'hyperf/constants'          => [
            'version' => '~2.1.0',
        ],
        'hyperf/json-rpc'           => [
            'version' => '~2.1.0',
        ],
        'hyperf/redis'              => [
            'version' => '~2.1.0',
        ],
        'hyperf/rpc'                => [
            'version' => '~2.1.0',
        ],
        'hyperf/rpc-client'         => [
            'version' => '~2.1.0',
        ],
        'hyperf/rpc-server'         => [
            'version' => '~2.1.0',
        ],
        'hyperf/grpc-client'        => [
            'version' => '~2.1.0',
        ],
        'hyperf/grpc-server'        => [
            'version' => '~2.1.0',
        ],
        'hyperf/elasticsearch'      => [
            'version' => '~2.1.0',
        ],
        'hyperf/config-apollo'      => [
            'version' => '~2.1.0',
        ],
        'hyperf/config-aliyun-acm'  => [
            'version' => '~2.1.0',
        ],
        'hyperf/config-etcd'        => [
            'version' => '~2.1.0',
        ],
        'hyperf/tracer'             => [
            'version' => '~2.1.0',
        ],
        'hyperf/service-governance' => [
            'version' => '~2.1.0',
        ],
        'hyperf/validation'         => [
            'version' => '~2.1.0',
        ],
        'hyperf/session'            => [
            'version' => '~2.1.0',
        ],
        'hyperf/view'               => [
            'version' => '~2.1.0',
        ],
        'hyperf/view-engine'        => [
            'version' => '~2.1.0',
        ],
        'hyperf/task'               => [
            'version' => '~2.1.0',
        ],
        'overtrue/wechat'           => [
            'version' => '^5.5',
        ],
        'wilbur-yu/hyperf-options'  => [
            'version' => '^1.0',
        ],
    ],
    'require-dev' => [
    ],
    'questions'   => [
        'database'      => [
            'question'       => 'Do you want to use Database (MySQL Client) ?',
            'default'        => 'y',
            'required'       => false,
            'force'          => false,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/database',
                        'hyperf/db-connection',
                    ],
                    'resources' => [
                        'resources/database/databases.php' => 'config/autoload/databases.php',
                    ],
                ],
            ],
        ],
        'options'       => [
            'question'       => 'Do you want to use wilbur-yu/options component ?',
            'default'        => 'y',
            'required'       => false,
            'force'          => false,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'wilbur-yu/options',
                    ],
                    'resources' => [
                        'resources/options/options.php'              => 'config/autoload/options.php',
                        'resources/options/create_options_table.php' => "migrations/{$timestamp}_create_options_table.php",
                    ],
                ],
            ],
        ],
        'redis'         => [
            'question'       => 'Do you want to use Redis Client ?',
            'default'        => 'y',
            'required'       => false,
            'force'          => false,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/redis',
                    ],
                    'resources' => [
                        'resources/database/redis/redis.php'         => 'config/autoload/redis.php',
                        'resources/database/redis/BitmapService.php' => 'app/Service/BitmapService.php',
                    ],
                ],
            ],
        ],
        'async-queue'   => [
            'question'       => 'Do you want to use hyperf/async-queue component ? (A simple redis queue component)',
            'default'        => 'n',
            'required'       => false,
            'force'          => true,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/async-queue',
                    ],
                    'resources' => [
                        'resources/async_queue/async_queue.php'         => 'config/autoload/async_queue.php',
                        'resources/async_queue/AsyncQueueConsumer.php'  => 'app/Process/AsyncQueueConsumer.php',
                        'resources/async_queue/QueueHandleListener.php' => 'app/Listener/QueueHandleListener.php',
                        'resources/database/redis.php'                  => 'config/autoload/redis.php',
                    ],
                ],
            ],
        ],
        'model-cache'   => [
            'question'       => 'Do you want to use hyperf/model-cache component ?',
            'default'        => 'n',
            'required'       => false,
            'force'          => true,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/model-cache',
                    ],
                    'resources' => [
                        'resources/model_cache/Model.php'     => 'app/Model/Model.php',
                        'resources/model_cache/databases.php' => 'config/autoload/databases.php',
                        'resources/database/redis.php'        => 'config/autoload/redis.php',
                    ],
                ],
            ],
        ],
        'view'          => [
            'question'       => 'Do you want to use hyperf/view component ?',
            'default'        => 'n',
            'required'       => false,
            'force'          => true,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/view',
                        'hyperf/view-engine',
                        'hyperf/task',
                    ],
                    'resources' => [
                        'resources/view/view.php'        => 'config/autoload/view.php',
                        'resources/view/storage/views'   => 'storage/views',
                        'resources/task/server.php'      => 'config/autoload/server.php',
                    ],
                ],
            ],
        ],
        'session'       => [
            'question'       => 'Do you want to use hyperf/session component ?',
            'default'        => 'n',
            'required'       => false,
            'force'          => false,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/session',
                    ],
                    'resources' => [
                        'resources/session/session.php'           => 'config/autoload/session.php',
                        'resources/session/SessionMiddleware.php' => 'app/Middleware/SessionMiddleware.php',
                    ],
                ],
            ],
        ],
        'wechat'        => [
            'question'       => 'Do you want to use hyperf/view component ?',
            'default'        => 'n',
            'required'       => false,
            'force'          => false,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'overtrue/wechat',
                    ],
                    'resources' => [
                        'resources/wechat/wechat.php'                   => 'config/autoload/wechat.php',
                        'resources/wechat/Factory/WechatFactory.php'    => 'app/Factory/WechatFactory.php',
                    ],
                ],
            ],
        ],
        'validation'    => [
            'question'       => 'Do you want to use hyperf/validation component ?',
            'default'        => 'n',
            'required'       => false,
            'force'          => true,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/validation',
                    ],
                    'resources' => [
                        'resources/validation/Request'        => 'app/Request',
                        'resources/validation/exceptions.php' => 'config/autoload/exceptions.php',
                        'resources/validation/middleware.php' => 'config/autoload/middleware.php',
                    ],
                ],
            ],
        ],
        'rpc'           => [
            'question'       => 'Which RPC protocol do you want to use ?',
            'default'        => 'n',
            'required'       => false,
            'custom-package' => true,
            'options'        => [
                1 => [
                    'name'      => 'JSON RPC with Service Governance',
                    'packages'  => [
                        'hyperf/json-rpc',
                        'hyperf/rpc',
                        'hyperf/rpc-client',
                        'hyperf/rpc-server',
                        'hyperf/service-governance',
                    ],
                    'resources' => [
                    ],
                ],
                2 => [
                    'name'      => 'JSON RPC',
                    'packages'  => [
                        'hyperf/json-rpc',
                        'hyperf/rpc',
                        'hyperf/rpc-client',
                        'hyperf/rpc-server',
                    ],
                    'resources' => [
                        'resources/jsonrpc/services.php' => 'config/autoload/services.php',
                    ],
                ],
                3 => [
                    'name'      => 'gRPC',
                    'packages'  => [
                        'hyperf/grpc-client',
                        'hyperf/grpc-server',
                    ],
                    'resources' => [
                    ],
                ],
            ],
        ],
        'config-center' => [
            'question'       => 'Which config center do you want to use ?',
            'default'        => 'n',
            'required'       => false,
            'custom-package' => true,
            'options'        => [
                1 => [
                    'name'      => 'Apollo',
                    'packages'  => [
                        'hyperf/config-apollo',
                    ],
                    'resources' => [
                        'resources/config_center/apollo.php' => 'config/autoload/apollo.php',
                    ],
                ],
                2 => [
                    'name'      => 'Aliyun ACM',
                    'packages'  => [
                        'hyperf/config-aliyun-acm',
                    ],
                    'resources' => [
                        'resources/config_center/aliyun_acm.php' => 'config/autoload/aliyun_acm.php',
                    ],
                ],
                3 => [
                    'name'      => 'ETCD',
                    'packages'  => [
                        'hyperf/config-etcd',
                    ],
                    'resources' => [
                        'resources/config_center/etcd.php'        => 'config/autoload/etcd.php',
                        'resources/config_center/config_etcd.php' => 'config/autoload/config_etcd.php',
                    ],
                ],
            ],
        ],
        'constants'     => [
            'question'       => 'Do you want to use hyperf/constants component ?',
            'default'        => 'y',
            'required'       => false,
            'force'          => true,
            'custom-package' => false,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/constants',
                    ],
                    'resources' => [
                        'resources/constants/ErrorCode.php'         => 'app/Constants/ErrorCode.php',
                        'resources/constants/BusinessException.php' => 'app/Exception/BusinessException.php',
                    ],
                ],
            ],
        ],
        'amqp'          => [
            'question'       => 'Do you want to use hyperf/amqp component ?',
            'default'        => 'n',
            'required'       => false,
            'force'          => true,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/amqp',
                    ],
                    'resources' => [
                        'resources/amqp/amqp.php' => 'config/autoload/amqp.php',
                    ],
                ],
            ],
        ],
        'elasticsearch' => [
            'question'       => 'Do you want to use hyperf/elasticsearch component ?',
            'default'        => 'n',
            'required'       => false,
            'force'          => true,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/elasticsearch',
                    ],
                    'resources' => [
                    ],
                ],
            ],
        ],
        'opentracing'   => [
            'question'       => 'Do you want to use hyperf/tracer component ? (An open tracing protocol component, adapte with Zipkin etc.)',
            'default'        => 'n',
            'required'       => false,
            'force'          => true,
            'custom-package' => true,
            'options'        => [
                'y' => [
                    'name'      => 'yes',
                    'packages'  => [
                        'hyperf/tracer',
                    ],
                    'resources' => [
                        'resources/tracer/opentracing.php' => 'config/autoload/opentracing.php',
                    ],
                ],
            ],
        ],
    ],
];
