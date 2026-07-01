<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreatePurchaseOrderFromRequestRequest;
use App\Http\Requests\Finance\StorePurchaseOrderRequest;
use App\Http\Requests\Finance\UpdatePurchaseOrderRequest;
use App\Http\Resources\Finance\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseService $service) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        return PurchaseOrderResource::collection($this->service->listOrders());
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        $order = $this->service->createOrder($request->validated());

        return response()->json(
            new PurchaseOrderResource($order->load(['vendor', 'purchaseRequest'])),
            201
        );
    }

    public function createFromRequest(
        CreatePurchaseOrderFromRequestRequest $request,
        PurchaseRequest $purchaseRequest
    ): JsonResponse {
        $this->authorize('create', PurchaseOrder::class);
        $this->authorize('view', $purchaseRequest);

        $vendor = Vendor::query()->findOrFail($request->validated('vendor_id'));

        $order = $this->service->createOrderFromRequest($purchaseRequest, $vendor);

        return response()->json(
            new PurchaseOrderResource($order->load(['vendor', 'purchaseRequest'])),
            201
        );
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);

        return response()->json(
            new PurchaseOrderResource(
                $this->service->updateOrder($purchaseOrder, $request->validated())
            )
        );
    }
}
