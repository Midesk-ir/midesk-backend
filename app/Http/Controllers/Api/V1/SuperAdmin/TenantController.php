<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Tenant::query()->with('plan')->orderByDesc('created_at')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug',
            'region' => 'in:IR,INTL',
            'plan_id' => 'nullable|uuid',
        ]);

        return response()->json(Tenant::create($data), 201);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json($tenant->load(['plan', 'subscriptions']));
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $tenant->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'region' => 'in:IR,INTL',
            'plan_id' => 'nullable|uuid',
        ]));

        return response()->json($tenant->fresh());
    }

    public function suspend(Tenant $tenant): JsonResponse
    {
        Subscription::query()->where('tenant_id', $tenant->id)->update(['status' => 'suspended']);

        return response()->json(['message' => 'Tenant suspended']);
    }
}

