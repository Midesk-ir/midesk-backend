<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreVendorRequest;
use App\Http\Requests\Finance\UpdateVendorRequest;
use App\Http\Resources\Finance\VendorResource;
use App\Models\Vendor;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VendorController extends Controller
{
    public function __construct(private PurchaseService $service) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Vendor::class);

        return VendorResource::collection($this->service->listVendors());
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        $this->authorize('create', Vendor::class);

        $vendor = $this->service->createVendor($request->validated());

        return response()->json(new VendorResource($vendor), 201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        $this->authorize('view', $vendor);

        return response()->json(new VendorResource($vendor));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $this->authorize('update', $vendor);

        return response()->json(
            new VendorResource($this->service->updateVendor($vendor, $request->validated()))
        );
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $this->authorize('delete', $vendor);

        $this->service->deleteVendor($vendor);

        return response()->json(null, 204);
    }
}
