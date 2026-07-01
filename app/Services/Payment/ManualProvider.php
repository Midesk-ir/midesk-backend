<?php

namespace App\Services\Payment;

class ManualProvider implements PaymentProvider
{
    public function createPayment(float $amount, string $currency, string $callbackUrl, array $meta = []): PaymentRedirect
    {
        return new PaymentRedirect($callbackUrl.'?manual=1', 'MANUAL-'.uniqid());
    }

    public function verifyPayment(string $authority): PaymentResult
    {
        return new PaymentResult(true, $authority, 'Manual payment recorded');
    }
}
