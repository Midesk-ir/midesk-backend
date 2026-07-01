<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PerformanceScoreFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceScore extends Model
{
    /** @use HasFactory<PerformanceScoreFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'indicator_id',
        'period',
        'score',
        'evidence',
        'evaluator_user_id',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<PerformanceIndicator, $this> */
    public function indicator(): BelongsTo
    {
        return $this->belongsTo(PerformanceIndicator::class, 'indicator_id');
    }

    /** @return BelongsTo<User, $this> */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }
}
