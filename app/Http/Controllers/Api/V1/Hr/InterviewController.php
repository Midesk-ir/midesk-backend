<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\InterviewResource;
use App\Models\Interview;
use App\Services\RecruitmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InterviewController extends Controller
{
    public function __construct(private RecruitmentService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Interview::class);

        return InterviewResource::collection(
            $this->service->listInterviews($request->only(['candidate_id']))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage', Interview::class);

        $data = $request->validate([
            'candidate_id' => ['required', 'uuid', 'exists:candidates,id'],
            'interviewer_user_id' => ['required', 'uuid', 'exists:users,id'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer'],
            'type' => ['nullable', 'string'],
        ]);

        $interview = $this->service->createInterview($data);

        return response()->json(new InterviewResource($interview->load(['candidate', 'interviewer'])), 201);
    }

    public function update(Request $request, Interview $interview): JsonResponse
    {
        $this->authorize('manage', Interview::class);

        $data = $request->validate([
            'scheduled_at' => ['sometimes', 'date'],
            'duration_minutes' => ['nullable', 'integer'],
            'type' => ['nullable', 'string'],
            'feedback' => ['nullable', 'string'],
            'score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'status' => ['nullable', 'string'],
        ]);

        return response()->json(new InterviewResource($this->service->updateInterview($interview, $data)));
    }
}
