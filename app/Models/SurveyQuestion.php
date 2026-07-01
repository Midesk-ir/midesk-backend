<?php

namespace App\Models;

use App\Enums\SurveyQuestionType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestion extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'title',
        'type',
        'options',
        'is_required',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'type' => SurveyQuestionType::class,
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    /** @return BelongsTo<Survey, $this> */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}
