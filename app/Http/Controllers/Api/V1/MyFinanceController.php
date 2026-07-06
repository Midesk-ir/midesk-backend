<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\ExpenseResource;
use App\Http\Resources\Finance\PaymentResource;
use App\Http\Resources\Finance\PayrollRecordResource;
use App\Models\Expense;
use App\Models\Payment;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MyFinanceController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function payslips(Request $request): AnonymousResourceCollection
    {
        $records = $this->payrollService->payslipsForUser($request->user()->id);

        return PayrollRecordResource::collection(
            $records->load('contract')
        );
    }

    public function showPayslip(string $id): JsonResponse
    {
        $record = $this->payrollService->payslipsForUser(request()->user()->id)
            ->firstWhere('id', $id);

        abort_unless($record, 404);

        return response()->json(['data' => new PayrollRecordResource($record->load('contract'))]);
    }

    public function expenses(Request $request): AnonymousResourceCollection
    {
        $expenses = Expense::query()
            ->with(['category', 'costCenter', 'budget'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return ExpenseResource::collection($expenses);
    }

    public function receipts(Request $request): AnonymousResourceCollection
    {
        $payments = Payment::query()
            ->with('paidBy')
            ->whereHasMorph('payable', [Expense::class], function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->orderByDesc('paid_at')
            ->get();

        return PaymentResource::collection($payments);
    }
}
