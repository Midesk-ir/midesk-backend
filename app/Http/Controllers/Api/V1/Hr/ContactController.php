<?php

namespace App\Http\Controllers\Api\V1\Hr;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Services\PersonnelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(private PersonnelService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $request->validate(['search' => ['required', 'string', 'min:1']]);

        return response()->json([
            'data' => $this->service->contacts($request->input('search')),
        ]);
    }
}
