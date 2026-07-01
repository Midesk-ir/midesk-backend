<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\OwnerReviewPurchaseRequestRequest;
use App\Http\Requests\Finance\StorePurchaseRequestRequest;
use App\Http\Requests\Finance\UpdatePurchaseRequestRequest;
use App\Http\Resources\Finance\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseRequestController extends Controller
{
    public function __construct(private PurchaseService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        return PurchaseRequestResource::collection(
            $this->service->listRequests($request->only(['status']))
        );
    }

    public function store(StorePurchaseRequestRequest $request): JsonResponse
    {
        $this->authorize('create', PurchaseRequest::class);

        $purchaseRequest = $this->service->createRequest(
            $request->user(),
            $request->validated()
        );

        return response()->json(
            new PurchaseRequestResource($purchaseRequest->load(['user', 'costCenter', 'budget'])),
            201
        );
    }

    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('view', $purchaseRequest);

        return response()->json(
            new PurchaseRequestResource(
                $purchaseRequest->load(['user', 'costCenter', 'budget', 'purchaseOrder'])
            )
        );
    }

    public function update(UpdatePurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('update', $purchaseRequest);

        return response()->json(
            new PurchaseRequestResource(
                $this->service->updateRequest($purchaseRequest, $request->validated())
            )
        );
    }

    public function submit(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('submit', $purchaseRequest);

        return response()->json(
            new PurchaseRequestResource(
                $this->service->submit($purchaseRequest)->load(['user', 'costCenter', 'budget'])
            )
        );
    }

    public function ownerReview(OwnerReviewPurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('ownerReview', $purchaseRequest);

        $validated = $request->validated();

        $purchaseRequest = $this->service->ownerReview(
            $purchaseRequest,
            $request->user(),
            $validated['action'],
            $validated['reason'] ?? null
        );

        return response()->json(
            new PurchaseRequestResource($purchaseRequest->load(['user', 'costCenter', 'budget']))
        );
    }

    public function financeReview(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('financeReview', $purchaseRequest);

        return response()->json(
            new PurchaseRequestResource(
                $this->service->financeReview($purchaseRequest)->load(['user', 'costCenter', 'budget'])
            )
        );
    }

    public function cancel(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('cancel', $purchaseRequest);

        return response()->json(
            new PurchaseRequestResource(
                $this->service->cancel($purchaseRequest)->load(['user', 'costCenter', 'budget'])
            )
        );
    }
}
