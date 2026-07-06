<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = PlatformSetting::current();

        return response()->json(['data' => $this->toPayload($settings)]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'platform_name' => 'sometimes|string|max:255',
            'support_email' => 'sometimes|email|max:255',
            'default_trial_days' => 'sometimes|integer|min:0|max:365',
            'maintenance_mode' => 'sometimes|boolean',
        ]);

        $settings = PlatformSetting::current();
        $settings->update($data);

        return response()->json(['data' => $this->toPayload($settings->fresh())]);
    }

    /** @return array<string, mixed> */
    private function toPayload(PlatformSetting $settings): array
    {
        return [
            'platform_name' => $settings->platform_name,
            'support_email' => $settings->support_email,
            'default_trial_days' => $settings->default_trial_days,
            'maintenance_mode' => $settings->maintenance_mode,
        ];
    }
}
