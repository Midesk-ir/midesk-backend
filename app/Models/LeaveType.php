<?php

namespace App\Models;

use App\Enums\LeaveUnit;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'default_balance_days',
        'unit',
        'is_paid',
        'requires_approval',
        'max_consecutive_days',
    ];

    protected function casts(): array
    {
        return [
            'default_balance_days' => 'decimal:2',
            'unit' => LeaveUnit::class,
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    /** @return HasMany<LeaveBalance, $this> */
    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /** @return HasMany<LeavePolicy, $this> */
    public function policies(): HasMany
    {
        return $this->hasMany(LeavePolicy::class);
    }
}
