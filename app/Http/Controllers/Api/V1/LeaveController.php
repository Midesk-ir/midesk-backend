<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\RejectLeaveRequestRequest;
use App\Http\Requests\Leave\StoreLeaveRequestRequest;
use App\Http\Requests\Leave\StoreLeaveTypeRequest;
use App\Http\Requests\Leave\UpdateLeaveTypeRequest;
use App\Http\Resources\LeaveBalanceResource;
use App\Http\Resources\LeaveRequestResource;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        private readonly LeaveService $leaveService,
    ) {}

    public function types(): JsonResponse
    {
        $this->authorize('viewAny', LeaveType::class);

        $types = LeaveType::query()->orderBy('name')->get();

        return response()->json(['data' => LeaveTypeResource::collection($types)]);
    }

    public function storeType(StoreLeaveTypeRequest $request): JsonResponse
    {
        $this->authorize('create', LeaveType::class);

        $type = $this->leaveService->createLeaveType($request->validated());

        return response()->json(new LeaveTypeResource($type), 201);
    }

    public function updateType(UpdateLeaveTypeRequest $request, LeaveType $leaveType): JsonResponse
    {
        $this->authorize('update', $leaveType);

        $type = $this->leaveService->updateLeaveType($leaveType, $request->validated());

        return response()->json(new LeaveTypeResource($type));
    }

    public function destroyType(LeaveType $leaveType): JsonResponse
    {
        $this->authorize('delete', $leaveType);

        $this->leaveService->deleteLeaveType($leaveType);

        return response()->json(null, 204);
    }

    public function myBalance(Request $request): JsonResponse
    {
        $balances = $this->leaveService->balancesForUser($request->user());

        return response()->json(['data' => LeaveBalanceResource::collection($balances)]);
    }

    public function userBalance(User $user): JsonResponse
    {
        $this->authorize('viewAny', LeaveRequest::class);

        if (! app('membership')?->isManagerOrAbove()) {
            abort(403);
        }

        $balances = $this->leaveService->balancesForUser($user);

        return response()->json(['data' => LeaveBalanceResource::collection($balances)]);
    }

    public function requests(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $items = $this->leaveService->listRequests($request->user(), true);

        return response()->json(['data' => LeaveRequestResource::collection($items)]);
    }

    public function myRequests(Request $request): JsonResponse
    {
        $items = $this->leaveService->myRequests($request->user());

        return response()->json(['data' => LeaveRequestResource::collection($items)]);
    }

    public function storeRequest(StoreLeaveRequestRequest $request): JsonResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $leaveRequest = $this->leaveService->createRequest($request->user(), $request->validated());

        return response()->json(
            new LeaveRequestResource($leaveRequest->load('leaveType')),
            201
        );
    }

    public function approve(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('review', $leaveRequest);

        $leaveRequest = $this->leaveService->approve($leaveRequest, request()->user());

        return response()->json(new LeaveRequestResource($leaveRequest->load(['leaveType', 'user'])));
    }

    public function reject(RejectLeaveRequestRequest $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('review', $leaveRequest);

        $leaveRequest = $this->leaveService->reject(
            $leaveRequest,
            $request->user(),
            $request->validated('reason')
        );

        return response()->json(new LeaveRequestResource($leaveRequest->load(['leaveType', 'user'])));
    }

    public function cancel(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('cancel', $leaveRequest);

        $leaveRequest = $this->leaveService->cancel($leaveRequest, request()->user());

        return response()->json(new LeaveRequestResource($leaveRequest->load('leaveType')));
    }
}
