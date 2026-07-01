<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\LeavePolicyResource;
use App\Models\LeavePolicy;
use App\Services\LeavePolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeavePolicyController extends Controller
{
    public function __construct(private LeavePolicyService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LeavePolicy::class);

        return LeavePolicyResource::collection($this->service->list());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LeavePolicy::class);

        $data = $request->validate([
            'leave_type_id' => ['required', 'uuid', 'exists:leave_types,id'],
            'role' => ['nullable', 'string'],
            'auto_approve' => ['nullable', 'boolean'],
            'approval_chain' => ['nullable', 'array'],
            'min_notice_days' => ['nullable', 'integer', 'min:0'],
            'allow_negative_balance' => ['nullable', 'boolean'],
            'prorate_first_year' => ['nullable', 'boolean'],
        ]);

        $policy = $this->service->create($data);

        return response()->json(new LeavePolicyResource($policy->load('leaveType')), 201);
    }

    public function update(Request $request, LeavePolicy $policy): JsonResponse
    {
        $this->authorize('update', $policy);

        $data = $request->validate([
            'leave_type_id' => ['sometimes', 'uuid', 'exists:leave_types,id'],
            'role' => ['nullable', 'string'],
            'auto_approve' => ['nullable', 'boolean'],
            'approval_chain' => ['nullable', 'array'],
            'min_notice_days' => ['nullable', 'integer', 'min:0'],
            'allow_negative_balance' => ['nullable', 'boolean'],
            'prorate_first_year' => ['nullable', 'boolean'],
        ]);

        return response()->json(new LeavePolicyResource($this->service->update($policy, $data)));
    }

    public function destroy(LeavePolicy $policy): JsonResponse
    {
        $this->authorize('delete', $policy);

        $this->service->delete($policy);

        return response()->json(null, 204);
    }
}
