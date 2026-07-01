<?php

namespace App\Models;

use App\Enums\PerformanceDataSource;
use App\Enums\PerformanceIndicatorCategory;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PerformanceIndicatorFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceIndicator extends Model
{
    /** @use HasFactory<PerformanceIndicatorFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'category',
        'weight',
        'measurement_unit',
        'target_value',
        'data_source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'target_value' => 'decimal:2',
            'category' => PerformanceIndicatorCategory::class,
            'data_source' => PerformanceDataSource::class,
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<PerformanceScore, $this> */
    public function scores(): HasMany
    {
        return $this->hasMany(PerformanceScore::class, 'indicator_id');
    }
}
