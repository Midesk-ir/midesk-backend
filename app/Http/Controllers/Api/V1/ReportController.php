<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomReport;
use App\Services\ReportExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private ReportExportService $exportService) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => CustomReport::query()->orderByDesc('created_at')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'data_source' => 'required|in:tasks,projects,attendance,leave,expenses,payroll,personnel,worklogs',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'group_by' => 'nullable|string',
            'chart_type' => 'nullable|in:table,bar,line,pie,area',
        ]);

        $report = CustomReport::create([...$data, 'created_by_user_id' => $request->user()->id]);

        return response()->json($report, 201);
    }

    public function show(CustomReport $report): JsonResponse
    {
        return response()->json($report);
    }

    public function update(Request $request, CustomReport $report): JsonResponse
    {
        $report->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'group_by' => 'nullable|string',
            'chart_type' => 'nullable|in:table,bar,line,pie,area',
        ]));

        return response()->json($report->fresh());
    }

    public function destroy(CustomReport $report): JsonResponse
    {
        $report->delete();

        return response()->json(['message' => 'Report deleted']);
    }

    public function preview(Request $request): JsonResponse
    {
        $config = $request->validate([
            'data_source' => 'required|in:tasks,projects,attendance,leave,expenses,payroll,personnel,worklogs',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'group_by' => 'nullable|string',
        ]);

        return response()->json(['data' => $this->exportService->preview($config)]);
    }

    public function export(Request $request, CustomReport $report): BinaryFileResponse|JsonResponse
    {
        $format = $request->query('format', 'csv');
        $export = $this->exportService->export($report, $format);

        return response()->download($export['path'], basename($export['path']), ['Content-Type' => $export['mime']]);
    }

    public function schedule(Request $request, CustomReport $report): JsonResponse
    {
        $report->update(['schedule' => $request->validate(['schedule' => 'required|array'])['schedule']]);

        return response()->json($report->fresh());
    }

    public function dataSources(): JsonResponse
    {
        return response()->json(['data' => $this->exportService->dataSources()]);
    }
}
