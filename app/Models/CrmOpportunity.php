<?php

namespace App\Models;

use App\Enums\CrmOpportunityStage;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmOpportunity extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'title',
        'value',
        'currency',
        'stage',
        'probability',
        'expected_close_date',
        'assigned_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'stage' => CrmOpportunityStage::class,
            'expected_close_date' => 'date',
        ];
    }

    /** @return BelongsTo<CrmContact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** @return HasMany<CrmActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'opportunity_id');
    }
}
