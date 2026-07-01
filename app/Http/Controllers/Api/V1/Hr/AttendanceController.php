<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    public function checkIn(Request $request): JsonResponse
    {
        $this->authorize('checkIn', AttendanceRecord::class);

        $record = $this->service->checkIn($request->user());

        return response()->json(new AttendanceRecordResource($record));
    }

    public function checkOut(Request $request): JsonResponse
    {
        $this->authorize('checkIn', AttendanceRecord::class);

        $record = $this->service->checkOut($request->user());

        return response()->json(new AttendanceRecordResource($record));
    }

    public function startBreak(Request $request): JsonResponse
    {
        $this->authorize('checkIn', AttendanceRecord::class);

        $record = $this->service->startBreak($request->user());

        return response()->json(new AttendanceRecordResource($record));
    }

    public function endBreak(Request $request): JsonResponse
    {
        $this->authorize('checkIn', AttendanceRecord::class);

        $record = $this->service->endBreak($request->user());

        return response()->json(new AttendanceRecordResource($record));
    }

    public function my(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        return AttendanceRecordResource::collection(
            $this->service->myRecords(
                $request->user(),
                $request->input('from'),
                $request->input('to')
            )
        );
    }

    public function today(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewTeam', AttendanceRecord::class);

        return AttendanceRecordResource::collection($this->service->todayOrganization());
    }

    public function report(Request $request): JsonResponse
    {
        $this->authorize('viewTeam', AttendanceRecord::class);

        $report = $this->service->report(
            $request->input('user_id'),
            $request->integer('month') ?: null,
            $request->integer('year') ?: null,
        );

        return response()->json([
            'month' => $report['month'],
            'year' => $report['year'],
            'summary' => $report['summary'],
            'records' => AttendanceRecordResource::collection($report['records']),
        ]);
    }

    public function update(Request $request, AttendanceRecord $attendance): JsonResponse
    {
        $this->authorize('update', $attendance);

        $data = $request->validate([
            'check_in_at' => ['nullable', 'date'],
            'check_out_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $record = $this->service->manualUpdate($attendance, $data);

        return response()->json(new AttendanceRecordResource($record));
    }
}
