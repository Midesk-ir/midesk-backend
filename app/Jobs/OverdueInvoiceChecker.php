<?php

namespace App\Jobs;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Notifications\OverdueInvoiceNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class OverdueInvoiceChecker implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            app()->instance('tenant', $tenant);

            $invoices = Invoice::query()
                ->where('status', InvoiceStatus::Sent)
                ->whereDate('due_date', '<', now()->toDateString())
                ->get();

            foreach ($invoices as $invoice) {
                $invoice->update(['status' => InvoiceStatus::Overdue]);

                $financeUsers = $tenant->memberships()
                    ->whereIn('role', ['OWNER', 'ADMIN', 'FINANCE'])
                    ->where('status', 'active')
                    ->with('user')
                    ->get()
                    ->pluck('user')
                    ->filter();

                Notification::send($financeUsers, new OverdueInvoiceNotification($invoice));
            }

            app()->forgetInstance('tenant');
        }
    }
}
