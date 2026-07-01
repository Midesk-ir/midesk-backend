<?php

namespace App\Services\Payment;

interface PaymentProvider
{
    public function createPayment(float $amount, string $currency, string $callbackUrl, array $meta = []): PaymentRedirect;

    public function verifyPayment(string $authority): PaymentResult;
}
