<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\PayInvoiceRequest;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Http\Requests\Finance\UpdateInvoiceRequest;
use App\Http\Resources\Finance\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Invoice::class);

        return InvoiceResource::collection(
            $this->service->list($request->only(['status']))
        );
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = $this->service->create($request->validated());

        return response()->json(new InvoiceResource($invoice->load('vendor')), 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        return response()->json(new InvoiceResource($invoice->load(['vendor', 'payments'])));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        return response()->json(
            new InvoiceResource($this->service->update($invoice, $request->validated()))
        );
    }

    public function pay(PayInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('pay', $invoice);

        return response()->json(
            new InvoiceResource(
                $this->service->pay($invoice, $request->user(), $request->validated())
                    ->load(['vendor', 'payments'])
            )
        );
    }
}
