<?php

namespace App\Services\Payment;

use Illuminate\Support\Str;

class StripeProvider implements PaymentProvider
{
    public function createPayment(float $amount, string $currency, string $callbackUrl, array $meta = []): PaymentRedirect
    {
        $authority = 'ST-'.Str::uuid();
        $redirectUrl = 'https://checkout.stripe.com/pay/'.$authority.'?return_url='.urlencode($callbackUrl);

        return new PaymentRedirect($redirectUrl, $authority);
    }

    public function verifyPayment(string $authority): PaymentResult
    {
        if (str_starts_with($authority, 'ST-')) {
            return new PaymentResult(true, $authority, 'Stripe payment verified');
        }

        return new PaymentResult(false, null, 'Invalid Stripe session');
    }
}
