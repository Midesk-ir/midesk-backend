<?php

namespace App\Models;

use App\Enums\CrmActivityType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmActivity extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'opportunity_id',
        'type',
        'description',
        'performed_by_user_id',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CrmActivityType::class,
            'performed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<CrmContact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }

    /** @return BelongsTo<CrmOpportunity, $this> */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(CrmOpportunity::class, 'opportunity_id');
    }

    /** @return BelongsTo<User, $this> */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
