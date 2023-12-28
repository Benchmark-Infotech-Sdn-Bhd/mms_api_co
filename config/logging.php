<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/lumen.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/lumen.log'),
            'level' => 'debug',
            'days' => 14,
        ],
         'cron_activity_logs' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cron_activity_logs.log'),
            'level' => 'debug',
            'days' => 14,
            'permission' => 0777,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Lumen Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'google-chat' => [
            'driver' => 'monolog',
            'url' => env('LOG_GOOGLE_CHAT_WEBHOOK_URL'),
            'notify_users' => [
                'default' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_DEFAULT'),
                'emergency' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_EMERGENCY'),
                'alert' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_ALERT'),
                'critical' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_CRITICAL'),
                'error' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_ERROR'),
                'warning' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_WARNING'),
                'notice' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_NOTICE'),
                'info' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_INFO'),
                'debug' => env('LOG_GOOGLE_CHAT_NOTIFY_USER_ID_DEBUG'),
            ],
            'level' => 'warning',
            'timezone' => env('LOG_GOOGLE_CHAT_TIMEZONE' , 'Asia/Kolkata'),
            'handler' => \Enigma\GoogleChatHandler::class,
        ],

    ],

];
