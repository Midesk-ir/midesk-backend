<?php

namespace App\Services\Payment;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public ?string $reference = null,
        public ?string $message = null,
    ) {}
}
