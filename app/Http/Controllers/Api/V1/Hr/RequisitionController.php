<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\RequisitionResource;
use App\Models\Requisition;
use App\Services\RecruitmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RequisitionController extends Controller
{
    public function __construct(private RecruitmentService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Requisition::class);

        return RequisitionResource::collection(
            $this->service->listRequisitions($request->only(['status']))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage', Requisition::class);

        $data = $request->validate([
            'title' => ['required', 'string'],
            'department' => ['nullable', 'string'],
            'positions_count' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
        ]);

        $requisition = $this->service->createRequisition($data, $request->user());

        return response()->json(new RequisitionResource($requisition), 201);
    }

    public function show(Requisition $requisition): JsonResponse
    {
        $this->authorize('viewAny', Requisition::class);

        return response()->json(new RequisitionResource($requisition->load('createdBy')));
    }

    public function update(Request $request, Requisition $requisition): JsonResponse
    {
        $this->authorize('manage', Requisition::class);

        $data = $request->validate([
            'title' => ['sometimes', 'string'],
            'department' => ['nullable', 'string'],
            'positions_count' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        return response()->json(new RequisitionResource($this->service->updateRequisition($requisition, $data)));
    }
}
