<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\KeyResultFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyResult extends Model
{
    /** @use HasFactory<KeyResultFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'objective_id',
        'title',
        'target_value',
        'current_value',
        'unit',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Objective, $this> */
    public function objective(): BelongsTo
    {
        return $this->belongsTo(Objective::class);
    }
}
