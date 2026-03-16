<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => env('MAIL_TIMEOUT', 60),
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'
            ),
        ],

        /*
        |--------------------------------------------------------------------------
        | Runtime Bulk Mailer Definitions
        |--------------------------------------------------------------------------
        |
        | These are overridden at runtime with Config::set(...) before sending.
        | Defining them here makes the mailers explicit and avoids edge cases.
        |
        */

        'bulk_mailer_campaign' => [
            'transport' => 'smtp',
            'scheme' => null,
            'url' => null,
            'host' => '127.0.0.1',
            'port' => 2525,
            'username' => null,
            'password' => null,
            'timeout' => 90,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'
            ),
        ],

        'bulk_mailer_campaign_test' => [
            'transport' => 'smtp',
            'scheme' => null,
            'url' => null,
            'host' => '127.0.0.1',
            'port' => 2525,
            'username' => null,
            'password' => null,
            'timeout' => 90,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'
            ),
        ],

        'bulk_mailer_test' => [
            'transport' => 'smtp',
            'scheme' => null,
            'url' => null,
            'host' => '127.0.0.1',
            'port' => 2525,
            'username' => null,
            'password' => null,
            'timeout' => 30,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'
            ),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

];