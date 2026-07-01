<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Models\HrSetting;
use App\Services\HrSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrSettingsController extends Controller
{
    public function __construct(private HrSettingsService $service) {}

    public function show(Request $request): JsonResponse
    {
        $this->authorize('viewAny', HrSetting::class);

        return response()->json($this->service->getSettings());
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorize('updateAny', HrSetting::class);

        $data = $request->validate([
            'attendance_grace_minutes' => ['nullable', 'integer', 'min:0'],
            'auto_checkout_enabled' => ['nullable', 'boolean'],
            'auto_checkout_time' => ['nullable', 'string'],
            'leave_year_start_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'contract_expiry_warning_days' => ['nullable', 'integer', 'min:1'],
            'currencies' => ['nullable', 'array'],
        ]);

        return response()->json($this->service->updateSettings($data));
    }

    public function currencies(Request $request): JsonResponse
    {
        $this->authorize('viewAny', HrSetting::class);

        return response()->json(['currencies' => $this->service->currencies()]);
    }
}
