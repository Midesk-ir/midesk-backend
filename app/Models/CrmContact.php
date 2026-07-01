<?php

namespace App\Models;

use App\Enums\CrmContactType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmContact extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'company',
        'email',
        'phone',
        'address',
        'type',
        'source',
        'assigned_user_id',
        'tags',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => CrmContactType::class,
            'tags' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** @return HasMany<CrmOpportunity, $this> */
    public function opportunities(): HasMany
    {
        return $this->hasMany(CrmOpportunity::class, 'contact_id');
    }
}
