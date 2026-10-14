<?php

return [
    'provider' => env('OTP_PROVIDER', 'log'),
    'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 2),
    'demo_code' => env('OTP_DEMO_CODE', '123456'),
    'use_demo_code' => env('OTP_PROVIDER', 'log') === 'log',
];
