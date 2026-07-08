<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central Server URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the Wardtech dashboard that status reports are sent to.
    | Every request is made relative to this value.
    |
    */

    'url' => env('WARDTECH_URL', 'https://apps.wardtech.co.uk'),

    /*
    |--------------------------------------------------------------------------
    | API Token
    |--------------------------------------------------------------------------
    |
    | Bearer token used to authenticate this application against the central
    | server. Keep it in your .env file — never commit it. This is the only
    | secret the package touches.
    |
    */

    'token' => env('WARDTECH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch. When false, `wardtech:report` exits without sending
    | anything. Handy for local/dev environments.
    |
    */

    'enabled' => env('WARDTECH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds to wait when talking to the central server.
    |
    */

    'timeout' => (int) env('WARDTECH_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    |
    | The package registers a daily report on the framework scheduler. Disable
    | it here if you would rather trigger `wardtech:report` yourself (e.g. from
    | a deploy hook or your own cron entry).
    |
    */

    'schedule' => [
        'enabled' => env('WARDTECH_SCHEDULE', true),
        'time' => env('WARDTECH_SCHEDULE_TIME', '03:00'),
    ],

];
