<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Performance\UpdateKeyResultProgressRequest;
use App\Http\Requests\Performance\UpdateKeyResultRequest;
use App\Http\Resources\Performance\KeyResultResource;
use App\Models\KeyResult;
use App\Services\OkrService;
use Illuminate\Http\JsonResponse;

class KeyResultController extends Controller
{
    public function __construct(private OkrService $service) {}

    public function update(UpdateKeyResultRequest $request, KeyResult $keyResult): JsonResponse
    {
        $this->authorize('update', $keyResult);

        return response()->json(
            new KeyResultResource($this->service->updateKeyResult($keyResult, $request->validated()))
        );
    }

    public function progress(UpdateKeyResultProgressRequest $request, KeyResult $keyResult): JsonResponse
    {
        $this->authorize('update', $keyResult);

        return response()->json(
            new KeyResultResource(
                $this->service->updateProgress($keyResult, (float) $request->validated('current_value'))
            )
        );
    }
}
