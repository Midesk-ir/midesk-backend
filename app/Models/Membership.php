<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'status',
        'invited_email',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => MembershipRole::class,
            'status' => MembershipStatus::class,
            'joined_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwnerOrAdmin(): bool
    {
        return in_array($this->role, [MembershipRole::Owner, MembershipRole::Admin], true);
    }

    public function isHrManager(): bool
    {
        return $this->role === MembershipRole::HrManager;
    }

    public function canManageHr(): bool
    {
        return in_array($this->role, [
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::HrManager,
        ], true);
    }

    public function isManagerOrAbove(): bool
    {
        return in_array($this->role, [
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
            MembershipRole::HrManager,
        ], true);
    }

    public function isFinance(): bool
    {
        return $this->role === MembershipRole::Finance;
    }

    public function canManageFinance(): bool
    {
        return in_array($this->role, [
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Finance,
        ], true);
    }

    public function canApprovePayroll(): bool
    {
        return in_array($this->role, [
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Finance,
            MembershipRole::HrManager,
        ], true);
    }
}
