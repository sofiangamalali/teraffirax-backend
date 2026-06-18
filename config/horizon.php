<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This value is the prefix that will be used when generating Horizon's
    | routes. You may specify a custom prefix if you wish to use a
    | sub-domain for the Horizon dashboard.
    |
    */

    'domain' => env('HORIZON_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API routes.
    |
    */

    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store its
    | metadata. This connection should be defined in your database config.
    |
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when keys are stored in Redis. This prevents
    | collision when multiple applications are using the same Redis server.
    |
    */

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply write your own.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when a queue is considered "long
    | waiting" based on wait time. When a queue exceeds this threshold
    | a notification will be sent.
    |
    */

    'waits' => [
        'redis:default' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long completed and failed jobs will be
    | stored in Redis before being deleted. This helps prevent Redis memory
    | from filling up.
    |
    */

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the metrics collected by Horizon. You may
    | specify which jobs and queues should have their metrics monitored.
    |
    */

    'metrics' => [
        'trim_defaults' => [
            'queue_time' => 10080,
            'job_time' => 10080,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's master command will terminate
    | immediately when receiving a termination signal instead of waiting
    | for the workers to finish.
    |
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit
    |--------------------------------------------------------------------------
    |
    | This value is the maximum amount of memory (in megabytes) that a Horizon
    | worker is allowed to consume before it is terminated.
    |
    */

    'memory_limit' => 64,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration Environments
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker configurations for each of your
    | environments. You can configure supervisors, worker processes, and
    | other options for each environment.
    |
    */

    'environments' => [
        'production' => [
            'telegram-selection-supervisor' => [
                'connection' => 'redis',
                'queue' => ['telegram-selection'],
                'balance' => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 1,
                'processes' => 1,
                'timeout' => 60,
            ],
            'telegram-validation-supervisor' => [
                'connection' => 'redis',
                'queue' => ['telegram-validation'],
                'balance' => 'simple',
                'minProcesses' => 2,
                'maxProcesses' => 2,
                'processes' => 2,
                'timeout' => 120,
            ],
            'telegram-publishing-supervisor' => [
                'connection' => 'redis',
                'queue' => ['telegram-publishing'],
                'balance' => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 1,
                'processes' => 1,
                'timeout' => 60,
            ],
        ],

        'local' => [
            'telegram-selection-supervisor' => [
                'connection' => 'redis',
                'queue' => ['telegram-selection'],
                'balance' => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 1,
                'processes' => 1,
                'timeout' => 60,
            ],
            'telegram-validation-supervisor' => [
                'connection' => 'redis',
                'queue' => ['telegram-validation'],
                'balance' => 'simple',
                'minProcesses' => 2,
                'maxProcesses' => 2,
                'processes' => 2,
                'timeout' => 120,
            ],
            'telegram-publishing-supervisor' => [
                'connection' => 'redis',
                'queue' => ['telegram-publishing'],
                'balance' => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 1,
                'processes' => 1,
                'timeout' => 60,
            ],
        ],
    ],
];
