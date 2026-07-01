<?php

namespace App\Models;

use App\Enums\AiRequestStatus;
use App\Enums\AiRequestType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRequest extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'type',
        'input_data',
        'result',
        'model_used',
        'tokens_used',
        'status',
        'error_message',
        'requested_by_user_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AiRequestType::class,
            'input_data' => 'array',
            'result' => 'array',
            'status' => AiRequestStatus::class,
            'created_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
