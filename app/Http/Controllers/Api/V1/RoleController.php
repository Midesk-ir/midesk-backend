<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomRole;
use App\Support\SitePermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function permissions(): JsonResponse
    {
        return response()->json(['data' => SitePermissions::ALL]);
    }

    public function indexCustom(): JsonResponse
    {
        return response()->json([
            'data' => CustomRole::query()->orderBy('name')->get(),
        ]);
    }

    public function storeCustom(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        $role = CustomRole::create([
            'tenant_id' => app('tenant')->id,
            'name' => $data['name'],
            'permissions' => $data['permissions'],
        ]);

        return response()->json(['data' => $role], 201);
    }

    public function updateCustom(Request $request, CustomRole $customRole): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string',
        ]);

        $customRole->update($data);

        return response()->json(['data' => $customRole->fresh()]);
    }

    public function destroyCustom(CustomRole $customRole): JsonResponse
    {
        $customRole->delete();

        return response()->json(null, 204);
    }
}
