<?php

namespace App\Http\Controllers\Webhooks;

use App\Actions\Webhooks\CaptureWebhookRequest;
use App\Http\Controllers\Controller;
use App\Models\WebhookBin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CaptureWebhookController extends Controller
{
    public function __invoke(Request $request, string $token, CaptureWebhookRequest $captureWebhookRequest): JsonResponse
    {
        $bin = WebhookBin::query()->where('token', $token)->first();

        if (! $bin instanceof WebhookBin) {
            return response()->json([
                'ok' => false,
                'captured' => false,
                'message' => 'Webhook bin not found.',
            ], 404);
        }

        $capturedRequest = $captureWebhookRequest($request, $bin);

        return response()->json([
            'ok' => true,
            'captured' => true,
            'id' => $capturedRequest->id,
            'received_at' => $capturedRequest->captured_at->toIso8601String(),
        ]);
    }
}
