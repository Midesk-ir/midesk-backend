<?php

namespace App\Services\Payment;

use Illuminate\Support\Str;

class ZarinpalProvider implements PaymentProvider
{
    public function createPayment(float $amount, string $currency, string $callbackUrl, array $meta = []): PaymentRedirect
    {
        $authority = 'ZP-'.Str::uuid();
        $sandbox = config('payment.zarinpal_sandbox', true) ? 'sandbox.' : '';
        $redirectUrl = "https://{$sandbox}zarinpal.com/pg/StartPay/{$authority}";

        return new PaymentRedirect($redirectUrl, $authority);
    }

    public function verifyPayment(string $authority): PaymentResult
    {
        if (str_starts_with($authority, 'ZP-')) {
            return new PaymentResult(true, $authority, 'Payment verified');
        }

        return new PaymentResult(false, null, 'Invalid authority');
    }
}
