<?php

return [
    'provider' => env('PAYMENT_PROVIDER', 'zarinpal'),
    'zarinpal_merchant_id' => env('ZARINPAL_MERCHANT_ID'),
    'zarinpal_sandbox' => env('ZARINPAL_SANDBOX', true),
    'stripe_key' => env('STRIPE_KEY'),
    'stripe_secret' => env('STRIPE_SECRET'),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
