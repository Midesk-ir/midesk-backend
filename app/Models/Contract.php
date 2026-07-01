<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'start_date',
        'end_date',
        'base_salary',
        'currency',
        'benefits',
        'status',
        'renewed_from_id',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'base_salary' => 'decimal:2',
            'benefits' => 'array',
            'type' => ContractType::class,
            'status' => ContractStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Contract, $this> */
    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'renewed_from_id');
    }
}
