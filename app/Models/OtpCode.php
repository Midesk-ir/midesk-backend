<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasUuids;

    protected $fillable = [
        'identifier',
        'code',
        'purpose',
        'expires_at',
        'is_used',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_used' => 'boolean',
        ];
    }

    public function isValid(string $code): bool
    {
        return ! $this->is_used
            && $this->code === $code
            && $this->expires_at->isFuture();
    }
}
