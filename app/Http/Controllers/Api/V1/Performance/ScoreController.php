<?php

namespace App\Http\Controllers\Api\V1\Performance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Performance\StorePerformanceScoreRequest;
use App\Http\Resources\Performance\PerformanceScoreResource;
use App\Models\PerformanceScore;
use App\Models\User;
use App\Services\PerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScoreController extends Controller
{
    public function __construct(private PerformanceService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PerformanceScore::class);

        return PerformanceScoreResource::collection(
            $this->service->listScores($request->only(['user_id', 'period', 'indicator_id']))
        );
    }

    public function store(StorePerformanceScoreRequest $request): JsonResponse
    {
        $this->authorize('create', PerformanceScore::class);

        $score = $this->service->recordScore($request->user(), $request->validated());

        return response()->json(
            new PerformanceScoreResource($score->load(['user', 'indicator', 'evaluator'])),
            201
        );
    }

    public function scoresSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PerformanceScore::class);

        $request->validate([
            'period' => ['required', 'string', 'max:20'],
        ]);

        return response()->json($this->service->scoresSummary($request->string('period')));
    }

    public function workSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PerformanceScore::class);

        $validated = $request->validate([
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'period' => ['required', 'string', 'max:20'],
        ]);

        $user = isset($validated['user_id'])
            ? User::query()->findOrFail($validated['user_id'])
            : $request->user();

        if ($user->id !== $request->user()->id && ! app('membership')?->isManagerOrAbove()) {
            abort(403);
        }

        return response()->json($this->service->workSummary($user, $validated['period']));
    }
}
