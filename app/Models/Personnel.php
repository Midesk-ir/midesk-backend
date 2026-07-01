<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\PersonnelStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PersonnelFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    /** @use HasFactory<PersonnelFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'personnel';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_code',
        'national_id',
        'birth_date',
        'gender',
        'marital_status',
        'address',
        'city',
        'province',
        'emergency_contact_name',
        'emergency_contact_phone',
        'hire_date',
        'termination_date',
        'employment_type',
        'status',
        'department',
        'job_title',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hire_date' => 'date',
            'termination_date' => 'date',
            'gender' => Gender::class,
            'marital_status' => MaritalStatus::class,
            'employment_type' => EmploymentType::class,
            'status' => PersonnelStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Contract, $this> */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'user_id', 'user_id');
    }

    /** @return HasMany<HrDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(HrDocument::class, 'user_id', 'user_id');
    }

    /** @return HasMany<AttendanceRecord, $this> */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'user_id', 'user_id');
    }
}
