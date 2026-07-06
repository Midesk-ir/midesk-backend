<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SiteCompatService;
use Illuminate\Http\JsonResponse;

class ModuleDashboardController extends Controller
{
    public function __construct(private readonly SiteCompatService $siteCompat) {}

    public function hr(): JsonResponse
    {
        return response()->json(['data' => $this->siteCompat->hrDashboard()]);
    }

    public function finance(): JsonResponse
    {
        return response()->json(['data' => $this->siteCompat->financeDashboard()]);
    }

    public function crm(): JsonResponse
    {
        return response()->json(['data' => $this->siteCompat->crmDashboard()]);
    }

    public function performance(): JsonResponse
    {
        return response()->json(['data' => $this->siteCompat->performanceDashboard()]);
    }
}
