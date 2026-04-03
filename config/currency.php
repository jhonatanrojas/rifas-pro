<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Currency API Configuration
    |--------------------------------------------------------------------------
    | Supported providers: exchangeratesapi.io, fixer.io, etc.
    | Set CURRENCY_API_URL and CURRENCY_API_KEY in .env
    */

    'api_url' => env('CURRENCY_API_URL'),
    'api_key' => env('CURRENCY_API_KEY'),

    'default_base' => env('CURRENCY_DEFAULT_BASE', 'USD'),
];
