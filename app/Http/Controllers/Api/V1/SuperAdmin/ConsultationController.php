<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ConsultationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(['total' => 0, 'pending' => 0, 'completed' => 0]);
    }
}
