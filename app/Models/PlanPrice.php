<?php

namespace App\Models;

use App\Enums\PlanRegion;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanPrice extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'region',
        'currency',
        'monthly_amount',
        'yearly_amount',
    ];

    protected function casts(): array
    {
        return [
            'region' => PlanRegion::class,
            'monthly_amount' => 'decimal:2',
            'yearly_amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Plan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
