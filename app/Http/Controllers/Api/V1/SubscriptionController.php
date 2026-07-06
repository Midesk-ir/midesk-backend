<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function current(Request $request): JsonResponse
    {
        $tenant = app('tenant');

        return response()->json([
            'data' => $this->subscriptionService->currentSubscription($tenant),
        ]);
    }

    public function plans(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->subscriptionService->plansWithPricing(app('tenant'))]);
    }

    public function changeRequest(Request $request): JsonResponse
    {
        $data = $request->validate(['plan_id' => 'required|uuid']);

        return response()->json(['message' => 'Plan change request submitted', 'plan_id' => $data['plan_id']], 202);
    }

    public function invoices(): JsonResponse
    {
        return response()->json(['data' => SubscriptionInvoice::query()->orderByDesc('created_at')->get()]);
    }

    public function pay(Request $request): JsonResponse
    {
        $data = $request->validate(['invoice_id' => 'required|uuid']);
        $invoice = SubscriptionInvoice::findOrFail($data['invoice_id']);
        $callback = url('/api/v1/subscription/verify');
        $result = $this->subscriptionService->initiatePayment($invoice, $callback);

        return response()->json($result);
    }

    public function verify(Request $request): JsonResponse
    {
        $authority = $request->query('authority') ?? $request->query('Authority');
        if (! $authority) {
            abort(422, 'Authority required');
        }

        $invoice = $this->subscriptionService->verifyPayment($authority);

        return response()->json($invoice);
    }
}
