<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Zarinpal = 'zarinpal';
    case Stripe = 'stripe';
    case Manual = 'manual';
}
