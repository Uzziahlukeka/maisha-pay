<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | MaishaPay API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your MaishaPay API credentials and settings.
    | You can obtain these from your MaishaPay merchant dashboard.
    |
    */

    'public_key' => env('MAISHAPAY_PUBLIC_KEY'),
    'secret_key' => env('MAISHAPAY_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Gateway Mode
    |--------------------------------------------------------------------------
    |
    | Set to 1 for production, 0 for sandbox/testing
    |
    */
    'gateway_mode' => env('MAISHAPAY_GATEWAY_MODE', 0),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for MaishaPay API endpoints
    |
    */
    'base_url' => env('MAISHAPAY_BASE_URL', 'https://marchand.maishapay.online/api/collect'),

    /*
    |--------------------------------------------------------------------------
    | B2C Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for MaishaPay B2C (Business to Customer) disbursement API.
    | This is a separate endpoint from the collection API.
    |
    */
    'b2c_base_url' => env('MAISHAPAY_B2C_BASE_URL', 'https://marchand.maishapay.online/api/b2c'),

    /*
    |--------------------------------------------------------------------------
    | Default Callback URL
    |--------------------------------------------------------------------------
    |
    | Default callback URL for payment notifications
    |
    */
    'callback_url' => env('MAISHAPAY_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Supported Mobile Money Providers
    |--------------------------------------------------------------------------
    */
    'mobile_money_providers' => [
        'AIRTEL',
        'ORANGE',
        'MTN',
        'VODACOM',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Card Providers
    |--------------------------------------------------------------------------
    */
    'card_providers' => [
        'VISA',
        'MASTERCARD',
        'AMERICAN EXPRESS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    */
    'currencies' => [
        'CDF', // Congolese Franc
        'USD', // US Dollar
        'EUR', // Euro
        'XAF', // Central African Franc
        'XOF',  // West African Franc
    ],
];
