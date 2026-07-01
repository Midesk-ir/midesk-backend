<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingTemplate extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'is_default',
        'steps',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'steps' => 'array',
        ];
    }

    /** @return HasMany<OnboardingProgress, $this> */
    public function progressRecords(): HasMany
    {
        return $this->hasMany(OnboardingProgress::class, 'template_id');
    }
}
