<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\BulkPayPayrollRequest;
use App\Http\Requests\Finance\CalculatePayrollRequest;
use App\Http\Resources\Finance\PayrollRecordResource;
use App\Models\PayrollRecord;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PayrollRecord::class);

        return PayrollRecordResource::collection(
            $this->service->list($request->only(['period', 'status']))
        );
    }

    public function calculate(CalculatePayrollRequest $request): AnonymousResourceCollection
    {
        $this->authorize('calculate', PayrollRecord::class);

        $records = $this->service->calculate($request->validated('period'));

        return PayrollRecordResource::collection(
            $records->load(['user', 'contract'])
        );
    }

    public function show(PayrollRecord $payrollRecord): JsonResponse
    {
        $this->authorize('view', $payrollRecord);

        return response()->json(
            new PayrollRecordResource($payrollRecord->load(['user', 'contract']))
        );
    }

    public function approve(PayrollRecord $payrollRecord): JsonResponse
    {
        $this->authorize('approve', $payrollRecord);

        return response()->json(
            new PayrollRecordResource(
                $this->service->approve($payrollRecord)->load(['user', 'contract'])
            )
        );
    }

    public function bulkPay(BulkPayPayrollRequest $request): AnonymousResourceCollection
    {
        $this->authorize('bulkPay', PayrollRecord::class);

        $records = $this->service->bulkPay($request->validated('ids'));

        return PayrollRecordResource::collection(
            $records->load(['user', 'contract'])
        );
    }
}
