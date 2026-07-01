<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AiRequestType;
use App\Http\Controllers\Controller;
use App\Models\AiRequest;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private AiService $aiService) {}

    public function analyzeTasks(Request $request): JsonResponse
    {
        $data = $request->validate([
            'project_id' => 'required|uuid',
            'prompt' => 'nullable|string',
        ]);

        return $this->queued(AiRequestType::TaskAnalysis, $data, $request);
    }

    public function generateDocument(Request $request): JsonResponse
    {
        $data = $request->validate([
            'project_id' => 'required|uuid',
            'template' => 'nullable|string',
            'prompt' => 'required|string',
        ]);

        return $this->queued(AiRequestType::DocumentGeneration, $data, $request);
    }

    public function screenResume(Request $request): JsonResponse
    {
        $data = $request->validate(['candidate_id' => 'required|uuid']);

        return $this->queued(AiRequestType::ResumeScreening, $data, $request);
    }

    public function analyzePerformance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|uuid',
            'period' => 'required|string',
        ]);

        return $this->queued(AiRequestType::PerformanceAnalysis, $data, $request);
    }

    public function query(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prompt' => 'required|string',
            'context_type' => 'nullable|string',
            'context_id' => 'nullable|uuid',
        ]);

        return $this->queued(AiRequestType::GeneralQuery, $data, $request);
    }

    public function show(AiRequest $aiRequest, Request $request): JsonResponse
    {
        if ($aiRequest->requested_by_user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json($aiRequest);
    }

    public function index(Request $request): JsonResponse
    {
        $items = AiRequest::query()
            ->where('requested_by_user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate();

        return response()->json($items);
    }

    private function queued(AiRequestType $type, array $data, Request $request): JsonResponse
    {
        $aiRequest = $this->aiService->queueRequest($type, $data, $request->user());

        return response()->json($aiRequest, 202);
    }
}
