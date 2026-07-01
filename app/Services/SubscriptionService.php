<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentGateway;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\Tenant;
use App\Services\Payment\ManualProvider;
use App\Services\Payment\PaymentProvider;
use App\Services\Payment\StripeProvider;
use App\Services\Payment\ZarinpalProvider;

class SubscriptionService
{
    public function paymentProvider(?PaymentGateway $gateway = null): PaymentProvider
    {
        $name = $gateway?->value ?? config('payment.provider', 'zarinpal');

        return match ($name) {
            'stripe' => new StripeProvider,
            'manual' => new ManualProvider,
            default => new ZarinpalProvider,
        };
    }

    public function currentSubscription(Tenant $tenant): ?Subscription
    {
        return Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first();
    }

    public function plansWithPricing(Tenant $tenant)
    {
        $region = is_object($tenant->region) ? $tenant->region->value : ($tenant->region ?? 'IR');

        return Plan::query()
            ->with(['prices' => fn ($q) => $q->where('region', $region)])
            ->get()
            ->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'features' => $plan->features,
                'pricing' => $plan->prices->first(),
            ]);
    }

    public function createInvoice(Subscription $subscription, float $amount, string $currency): SubscriptionInvoice
    {
        return SubscriptionInvoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'currency' => $currency,
            'status' => InvoiceStatus::Pending,
            'gateway' => PaymentGateway::from(config('payment.provider', 'zarinpal')),
            'due_date' => now()->addDays(7)->toDateString(),
            'created_at' => now(),
        ]);
    }

    public function initiatePayment(SubscriptionInvoice $invoice, string $callbackUrl): array
    {
        $provider = $this->paymentProvider($invoice->gateway);
        $redirect = $provider->createPayment(
            (float) $invoice->amount,
            $invoice->currency,
            $callbackUrl,
            ['invoice_id' => $invoice->id]
        );

        $invoice->update(['gateway_ref' => $redirect->authority]);

        return ['redirect_url' => $redirect->redirectUrl, 'authority' => $redirect->authority];
    }

    public function verifyPayment(string $authority): SubscriptionInvoice
    {
        $invoice = SubscriptionInvoice::query()->where('gateway_ref', $authority)->firstOrFail();
        $result = $this->paymentProvider($invoice->gateway)->verifyPayment($authority);

        if ($result->success) {
            $invoice->update(['status' => InvoiceStatus::Paid, 'paid_at' => now()]);
            $invoice->subscription?->update(['current_period_end' => now()->addMonth()]);
        } else {
            $invoice->update(['status' => InvoiceStatus::Failed]);
        }

        return $invoice->fresh();
    }
}
