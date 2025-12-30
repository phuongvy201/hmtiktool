<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TikTok Shop API Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình App Key và App Secret cho TikTok Shop API
    | Được cấu hình bởi system-admin và sử dụng chung cho tất cả teams
    |
    */

    'app_key' => env('TIKTOK_SHOP_APP_KEY', ''),

    'app_secret' => env('TIKTOK_SHOP_APP_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Market-specific Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình App Key và App Secret cho từng thị trường
    | US: Thị trường Hoa Kỳ
    | UK: Thị trường Vương quốc Anh
    |
    */

    'markets' => [
        'US' => [
            'app_key' => env('TIKTOK_SHOP_US_APP_KEY', ''),
            'app_secret' => env('TIKTOK_SHOP_US_APP_SECRET', ''),
        ],
        'UK' => [
            'app_key' => env('TIKTOK_SHOP_UK_APP_KEY', ''),
            'app_secret' => env('TIKTOK_SHOP_UK_APP_SECRET', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL cho TikTok Shop API
    |
    */

    'api_base_url' => env('TIKTOK_SHOP_API_BASE_URL', 'https://open-api.tiktokglobalshop.com'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình OAuth cho TikTok Shop
    |
    */

    'oauth' => [
        'authorization_url' => 'https://auth.tiktok-shops.com/oauth/authorize',
        'token_url' => 'https://auth.tiktok-shops.com/api/v2/token/get',
        'refresh_token_url' => 'https://auth.tiktok-shops.com/api/v2/token/refresh',
        'scope' => 'seller.authorization.info,seller.shop.info,seller.product.basic,seller.order.info,seller.fulfillment.basic,seller.logistics,seller.delivery.status.write,seller.finance.info,seller.product.delete,seller.product.write,seller.product.optimize,seller.analytics',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Các cài đặt mặc định cho TikTok Shop integration
    |
    */

    'defaults' => [
        'token_expiry_buffer' => 300, // 5 minutes buffer before token expires
        'max_retries' => 3,
        'timeout' => 30,
    ],
];
