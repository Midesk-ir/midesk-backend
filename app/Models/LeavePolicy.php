<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavePolicy extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'leave_type_id',
        'role',
        'auto_approve',
        'approval_chain',
        'min_notice_days',
        'allow_negative_balance',
        'prorate_first_year',
    ];

    protected function casts(): array
    {
        return [
            'role' => MembershipRole::class,
            'auto_approve' => 'boolean',
            'approval_chain' => 'array',
            'allow_negative_balance' => 'boolean',
            'prorate_first_year' => 'boolean',
        ];
    }

    /** @return BelongsTo<LeaveType, $this> */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
