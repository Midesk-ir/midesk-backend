<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Plan::query()->with('prices')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:plans,code',
            'features' => 'nullable|array',
        ]);

        return response()->json(Plan::create($data), 201);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $plan->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'features' => 'nullable|array',
        ]));

        return response()->json($plan->fresh());
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();

        return response()->json(['message' => 'Plan deleted']);
    }
}

