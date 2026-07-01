<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Enums\PersonnelStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\PersonnelResource;
use App\Models\Personnel;
use App\Services\PersonnelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PersonnelController extends Controller
{
    public function __construct(private PersonnelService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Personnel::class);

        return PersonnelResource::collection(
            $this->service->list($request->only(['status', 'department', 'search']))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Personnel::class);

        $data = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'employee_code' => ['required', 'string', 'max:50'],
            'national_id' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string'],
            'marital_status' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'province' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string'],
            'emergency_contact_phone' => ['nullable', 'string'],
            'hire_date' => ['required', 'date'],
            'employment_type' => ['required', 'string'],
            'status' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'job_title' => ['nullable', 'string'],
        ]);

        $personnel = $this->service->create([
            ...$data,
            'status' => $data['status'] ?? PersonnelStatus::Active->value,
        ]);

        return response()->json(new PersonnelResource($personnel->load('user')), 201);
    }

    public function show(Personnel $personnel): JsonResponse
    {
        $this->authorize('view', $personnel);

        return response()->json(new PersonnelResource($this->service->show($personnel)));
    }

    public function update(Request $request, Personnel $personnel): JsonResponse
    {
        $this->authorize('update', $personnel);

        $data = $request->validate([
            'employee_code' => ['sometimes', 'string', 'max:50'],
            'national_id' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string'],
            'marital_status' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'province' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string'],
            'emergency_contact_phone' => ['nullable', 'string'],
            'hire_date' => ['sometimes', 'date'],
            'termination_date' => ['nullable', 'date'],
            'employment_type' => ['sometimes', 'string'],
            'status' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'job_title' => ['nullable', 'string'],
        ]);

        return response()->json(new PersonnelResource($this->service->update($personnel, $data)));
    }

    public function destroy(Personnel $personnel): JsonResponse
    {
        $this->authorize('delete', $personnel);

        $this->service->delete($personnel);

        return response()->json(null, 204);
    }
}
