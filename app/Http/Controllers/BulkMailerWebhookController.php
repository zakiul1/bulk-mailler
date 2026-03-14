<?php

namespace App\Http\Controllers;

use App\Services\BulkMailerWebhookEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkMailerWebhookController extends Controller
{
    public function __invoke(string $provider, Request $request, BulkMailerWebhookEventService $service): JsonResponse
    {
        $payload = $request->all();

        if ($provider === 'ses' && isset($payload['Type']) && $payload['Type'] === 'SubscriptionConfirmation') {
            return response()->json([
                'message' => 'SES subscription confirmation received.',
            ]);
        }

        $service->ingest($provider, $payload);

        return response()->json([
            'message' => 'Webhook event processed.',
        ]);
    }
}