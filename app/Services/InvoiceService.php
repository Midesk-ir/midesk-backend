<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    /** @return Collection<int, Invoice> */
    public function list(array $filters = []): Collection
    {
        $query = Invoice::query()->with('vendor');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function create(array $data): Invoice
    {
        return Invoice::create([
            'tenant_id' => app('tenant')->id,
            'currency' => $data['currency'] ?? app('tenant')->default_currency,
            'status' => InvoiceStatus::Draft,
            ...$data,
        ]);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            throw ValidationException::withMessages([
                'invoice' => [__('errors.invoice_already_paid')],
            ]);
        }

        $invoice->update($data);

        return $invoice->fresh('vendor');
    }

    public function pay(Invoice $invoice, User $payer, array $data = []): Invoice
    {
        if (! in_array($invoice->status, [InvoiceStatus::Sent, InvoiceStatus::Overdue, InvoiceStatus::Draft], true)) {
            throw ValidationException::withMessages([
                'invoice' => [__('errors.invalid_workflow_transition')],
            ]);
        }

        return DB::transaction(function () use ($invoice, $payer, $data) {
            Payment::create([
                'tenant_id' => $invoice->tenant_id,
                'payable_type' => Invoice::class,
                'payable_id' => $invoice->id,
                'amount' => $invoice->amount,
                'currency' => $invoice->currency,
                'method' => $data['method'] ?? PaymentMethod::BankTransfer,
                'reference' => $data['reference'] ?? null,
                'paid_by_user_id' => $payer->id,
                'paid_at' => now(),
                'receipt_path' => $data['receipt_path'] ?? null,
            ]);

            $invoice->update([
                'status' => InvoiceStatus::Paid,
                'paid_at' => now(),
            ]);

            return $invoice->fresh(['vendor', 'payments']);
        });
    }
}
