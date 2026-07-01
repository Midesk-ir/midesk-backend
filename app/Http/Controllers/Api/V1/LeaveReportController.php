<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use App\Services\LeavePolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveReportController extends Controller
{
    public function __construct(private LeavePolicyService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('report', LeavePolicy::class);

        return response()->json($this->service->report(
            $request->input('user_id'),
            $request->input('leave_type_id'),
            $request->integer('month') ?: null,
            $request->integer('year') ?: null,
        ));
    }
}
