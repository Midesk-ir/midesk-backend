<?php

namespace App\Models;

use App\Enums\GuestRole;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestAccess extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'email',
        'name',
        'token',
        'role',
        'project_ids',
        'expires_at',
        'created_by_user_id',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => GuestRole::class,
            'project_ids' => 'array',
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
