<?php

return [
    'storage'                                   => env('LOCALIFY_STORAGE', 'database'),

    'drivers'                                   => [
        'database'  => [
            'driver'        => 'database',
            'connection'    => env('LOCALIFY_CONNECTION'),
            'tables'        => [
                'language'  => env('LOCALIFY_LANGUAGE_TABLE', 'localify_languages'),
                'currency'  => env('LOCALIFY_CURRENCY_TABLE', 'localify_currencies'),
                'timezone'  => env('LOCALIFY_TIMEZONE_TABLE', 'localify_timezones'),
            ]
        ],
    ],
];
