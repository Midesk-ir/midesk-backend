<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\CostCenterFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends Model
{
    /** @use HasFactory<CostCenterFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'parent_id',
        'manager_user_id',
    ];

    /** @return BelongsTo<CostCenter, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'parent_id');
    }

    /** @return BelongsTo<User, $this> */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    /** @return HasMany<CostCenter, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(CostCenter::class, 'parent_id');
    }

    /** @return HasMany<Budget, $this> */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
