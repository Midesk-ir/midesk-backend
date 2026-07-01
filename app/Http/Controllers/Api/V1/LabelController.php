<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Label\StoreLabelRequest;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LabelController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return LabelResource::collection(Label::query()->orderBy('name')->get());
    }

    public function store(StoreLabelRequest $request): JsonResponse
    {
        $label = Label::create([
            ...$request->validated(),
            'color' => $request->input('color', '#6366f1'),
        ]);

        return response()->json(new LabelResource($label), 201);
    }
}
