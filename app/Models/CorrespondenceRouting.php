<?php

namespace App\Models;

use App\Enums\RoutingAction;
use App\Enums\RoutingStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrespondenceRouting extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $table = 'correspondence_routing';

    protected $fillable = [
        'tenant_id',
        'correspondence_id',
        'from_user_id',
        'to_user_id',
        'action',
        'note',
        'status',
        'due_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => RoutingAction::class,
            'status' => RoutingStatus::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Correspondence, $this> */
    public function correspondence(): BelongsTo
    {
        return $this->belongsTo(Correspondence::class);
    }

    /** @return BelongsTo<User, $this> */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
