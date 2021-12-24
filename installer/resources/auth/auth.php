<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

use App\Auth\Guard\SsoGuard;
use App\Constants\BusConstant\GuardConstant;
use App\Model\Coach\Coach;
use App\Model\Student\Student;
use HyperfExt\Auth\Passwords\DatabaseTokenRepository;
use HyperfExt\Auth\UserProviders\ModelUserProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | AuthenticationCode Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'default' => [
        'guard' => GuardConstant::GUARD_STUDENT,
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | AuthenticationCode Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    */

    'guards' => [
        GuardConstant::GUARD_STUDENT => [
            'driver' => SsoGuard::class,
            'provider' => 'students',
            'options' => [],
        ],
        GuardConstant::GUARD_COACH => [
            'driver' => SsoGuard::class,
            'provider' => 'coaches',
            'options' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    */

    'providers' => [
        'students' => [
            'driver' => ModelUserProvider::class,
            'options' => [
                'model' => Student::class,
                'hash_driver' => 'bcrypt',
            ],
        ],
        'coaches' => [
            'driver' => ModelUserProvider::class,
            'options' => [
                'model' => Coach::class,
                'hash_driver' => 'bcrypt',
            ],
        ],

        // 'users' => [
        //     'driver' => \Hyperf\Auth\UserProvider\DatabaseUserProvider::class,
        //     'options' => [
        //         'connection' => 'default',
        //         'table' => 'users',
        //         'hash_driver' => 'bcrypt',
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'driver' => DatabaseTokenRepository::class,
            'provider' => 'users',
            'options' => [
                'connection' => null,
                'table' => 'password_resets',
                'expire' => 3600,
                'throttle' => 60,
                'hash_driver' => null,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | Access Gate Policies
    |--------------------------------------------------------------------------
    |
    */

    'policies' => [
        //Model::class => RefundPolicy::class,
    ],
];
