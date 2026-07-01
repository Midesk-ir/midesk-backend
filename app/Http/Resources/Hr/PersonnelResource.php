<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Personnel */
class PersonnelResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'employee_code' => $this->employee_code,
            'national_id' => $this->national_id,
            'birth_date' => $this->birth_date?->toDateString(),
            'gender' => $this->gender?->value,
            'marital_status' => $this->marital_status?->value,
            'address' => $this->address,
            'city' => $this->city,
            'province' => $this->province,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'hire_date' => $this->hire_date->toDateString(),
            'termination_date' => $this->termination_date?->toDateString(),
            'employment_type' => $this->employment_type?->value ?? $this->employment_type,
            'status' => $this->status?->value ?? $this->status,
            'department' => $this->department,
            'job_title' => $this->job_title,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
            ]),
            'contracts' => ContractResource::collection($this->whenLoaded('contracts')),
            'documents' => HrDocumentResource::collection($this->whenLoaded('documents')),
            'attendance_records' => AttendanceRecordResource::collection($this->whenLoaded('attendanceRecords')),
        ];
    }
}
