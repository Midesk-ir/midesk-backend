<?php

namespace App\Models;

use App\Enums\PayrollRecordStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PayrollRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    /** @use HasFactory<PayrollRecordFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'period',
        'contract_id',
        'base_salary',
        'overtime_hours',
        'overtime_amount',
        'allowances',
        'deductions',
        'gross_amount',
        'net_amount',
        'currency',
        'status',
        'calculated_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'allowances' => 'array',
            'deductions' => 'array',
            'gross_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'status' => PayrollRecordStatus::class,
            'calculated_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Contract, $this> */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
