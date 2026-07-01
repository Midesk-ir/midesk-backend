<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentGateway;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'amount',
        'currency',
        'status',
        'gateway',
        'gateway_ref',
        'paid_at',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'gateway' => PaymentGateway::class,
            'paid_at' => 'datetime',
            'due_date' => 'date',
        ];
    }

    /** @return BelongsTo<Subscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
