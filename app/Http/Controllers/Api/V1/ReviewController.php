<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Performance\StoreReviewRequest;
use App\Http\Requests\Performance\SubmitManagerReviewRequest;
use App\Http\Requests\Performance\SubmitSelfReviewRequest;
use App\Http\Requests\Performance\UpdateReviewRequest;
use App\Http\Resources\Performance\PerformanceReviewResource;
use App\Models\PerformanceReview;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PerformanceReview::class);

        return PerformanceReviewResource::collection(
            $this->service->list($request->only(['user_id', 'period', 'status']))
        );
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $this->authorize('create', PerformanceReview::class);

        $review = $this->service->create($request->validated());

        return response()->json(
            new PerformanceReviewResource($review->load(['user', 'reviewer'])),
            201
        );
    }

    public function show(PerformanceReview $review): JsonResponse
    {
        $this->authorize('view', $review);

        return response()->json(
            new PerformanceReviewResource($review->load(['user', 'reviewer']))
        );
    }

    public function update(UpdateReviewRequest $request, PerformanceReview $review): JsonResponse
    {
        $this->authorize('update', $review);

        return response()->json(
            new PerformanceReviewResource($this->service->update($review, $request->validated()))
        );
    }

    public function submitSelf(SubmitSelfReviewRequest $request, PerformanceReview $review): JsonResponse
    {
        $this->authorize('submitSelf', $review);

        return response()->json(
            new PerformanceReviewResource(
                $this->service->submitSelfAssessment(
                    $review,
                    $request->user(),
                    $request->validated('self_assessment_text')
                )->load(['user', 'reviewer'])
            )
        );
    }

    public function submitManager(SubmitManagerReviewRequest $request, PerformanceReview $review): JsonResponse
    {
        $this->authorize('submitManager', $review);

        return response()->json(
            new PerformanceReviewResource(
                $this->service->submitManagerFeedback(
                    $review,
                    $request->user(),
                    $request->validated()
                )->load(['user', 'reviewer'])
            )
        );
    }

    public function finalize(PerformanceReview $review): JsonResponse
    {
        $this->authorize('finalize', $review);

        return response()->json(
            new PerformanceReviewResource(
                $this->service->finalize($review)->load(['user', 'reviewer'])
            )
        );
    }
}
