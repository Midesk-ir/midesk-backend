<?php

namespace App\Services\Payment;

class PaymentRedirect
{
    public function __construct(
        public string $redirectUrl,
        public string $authority,
    ) {}
}
