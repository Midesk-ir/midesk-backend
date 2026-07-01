<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = SuperAdmin::query()->where('email', $data['email'])->first();

        if (! $admin || ! $admin->is_active || ! Hash::check($data['password'], $admin->password)) {
            return response()->json(['error' => ['code' => 'INVALID_CREDENTIALS', 'message' => 'Invalid credentials']], 401);
        }

        $admin->update(['last_login_at' => now()]);
        $token = $admin->createToken('super-admin')->plainTextToken;

        return response()->json(['admin' => $admin->only(['id', 'email', 'full_name']), 'token' => $token]);
    }
}

