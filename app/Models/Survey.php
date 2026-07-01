<?php

namespace App\Models;

use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'type',
        'is_anonymous',
        'status',
        'start_date',
        'end_date',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => SurveyType::class,
            'is_anonymous' => 'boolean',
            'status' => SurveyStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /** @return HasMany<SurveyQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order_index');
    }

    /** @return HasMany<SurveyResponse, $this> */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
