<?php

return [
    'storage'                                   => env('LOCALIFY_STORAGE', 'database'),

    'fallback_locale'                           => env('LOCALIFY_FALLBACK_LOCALE', 'en'),
    'fallback_currency'                         => env('LOCALIFY_FALLBACK_CURRENCY', 'USD'),
    'fallback_timezone'                         => env('LOCALIFY_FALLBACK_CURRENCY', 'UTC'),



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
