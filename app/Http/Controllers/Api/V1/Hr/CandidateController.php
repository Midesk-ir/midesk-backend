<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hr\CandidateResource;
use App\Http\Resources\Hr\OnboardingProgressResource;
use App\Http\Resources\Hr\PersonnelResource;
use App\Models\Candidate;
use App\Services\RecruitmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CandidateController extends Controller
{
    public function __construct(private RecruitmentService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Candidate::class);

        return CandidateResource::collection(
            $this->service->listCandidates($request->only(['status', 'requisition_id']))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage', Candidate::class);

        $data = $request->validate([
            'requisition_id' => ['nullable', 'uuid', 'exists:requisitions,id'],
            'full_name' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $candidate = $this->service->createCandidate($data);

        return response()->json(new CandidateResource($candidate), 201);
    }

    public function show(Candidate $candidate): JsonResponse
    {
        $this->authorize('viewAny', Candidate::class);

        return response()->json(new CandidateResource($candidate->load('requisition')));
    }

    public function update(Request $request, Candidate $candidate): JsonResponse
    {
        $this->authorize('manage', Candidate::class);

        $data = $request->validate([
            'full_name' => ['sometimes', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        return response()->json(new CandidateResource($this->service->updateCandidate($candidate, $data)));
    }

    public function hire(Request $request, Candidate $candidate): JsonResponse
    {
        $this->authorize('manage', Candidate::class);

        $personnelData = $request->validate([
            'employee_code' => ['nullable', 'string'],
            'hire_date' => ['nullable', 'date'],
            'department' => ['nullable', 'string'],
            'job_title' => ['nullable', 'string'],
        ]);

        $result = $this->service->hire($candidate, $personnelData);

        return response()->json([
            'personnel' => new PersonnelResource($result['personnel']),
            'onboarding' => new OnboardingProgressResource($result['onboarding']),
        ], 201);
    }

    public function resume(Candidate $candidate): JsonResponse
    {
        $this->authorize('viewAny', Candidate::class);

        return response()->json([
            'data' => [
                'id' => $candidate->id,
                'full_name' => $candidate->full_name,
                'resume_path' => $candidate->resume_path,
                'resume_text' => $candidate->notes,
            ],
        ]);
    }
}
