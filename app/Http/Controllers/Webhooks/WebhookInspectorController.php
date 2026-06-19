<?php

namespace App\Http\Controllers\Webhooks;

use App\Actions\Webhooks\CreateWebhookBin;
use App\Http\Controllers\Controller;
use App\Models\CapturedRequest;
use App\Models\WebhookBin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class WebhookInspectorController extends Controller
{
    public function index(Request $request, CreateWebhookBin $createWebhookBin): Response
    {
        $bin = $this->currentBin($request, $createWebhookBin);

        return Inertia::render('WebhookInspector', [
            'bin' => $this->presentBin($bin),
            'requests' => $this->presentRequests($bin),
            'limits' => $this->limits(),
            'urls' => [
                'bin' => route('inspector.bin'),
            ],
        ]);
    }

    public function bin(Request $request, CreateWebhookBin $createWebhookBin): JsonResponse
    {
        $bin = $this->currentBin($request, $createWebhookBin);

        return response()->json([
            'bin' => $this->presentBin($bin),
            'requests' => $this->presentRequests($bin),
            'limits' => $this->limits(),
        ]);
    }

    public function requests(WebhookBin $bin): JsonResponse
    {
        return response()->json([
            'requests' => $this->presentRequests($bin),
        ]);
    }

    public function clear(WebhookBin $bin): JsonResponse
    {
        $bin->capturedRequests()->delete();

        return response()->json([
            'requests' => [],
        ]);
    }

    private function currentBin(Request $request, CreateWebhookBin $createWebhookBin): WebhookBin
    {
        $token = $request->session()->get('webhook_bin_token');
        $bin = is_string($token)
            ? WebhookBin::query()->where('token', $token)->first()
            : null;

        if (! $bin instanceof WebhookBin) {
            $bin = $createWebhookBin();
            $request->session()->put('webhook_bin_token', $bin->token);
        }

        return $bin;
    }

    /**
     * @return array{
     *     token: string,
     *     webhook_url: string,
     *     requests_url: string,
     *     clear_url: string,
     *     created_at: string|null,
     *     last_captured_at: string|null
     * }
     */
    private function presentBin(WebhookBin $bin): array
    {
        return [
            'token' => $bin->token,
            'webhook_url' => route('webhooks.capture', ['token' => $bin->token]),
            'requests_url' => route('inspector.requests', ['bin' => $bin]),
            'clear_url' => route('inspector.requests.clear', ['bin' => $bin]),
            'created_at' => $bin->created_at?->toIso8601String(),
            'last_captured_at' => $bin->last_captured_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function presentRequests(WebhookBin $bin): array
    {
        $requests = $bin->capturedRequests()
            ->latest('captured_at')
            ->latest('id')
            ->limit((int) config('webhooks.max_requests_per_bin', 50))
            ->get()
            ->map(fn (CapturedRequest $request): array => $request->toInspectorArray())
            ->values()
            ->all();

        return array_values($requests);
    }

    /**
     * @return array{max_body_bytes: int, max_requests_per_bin: int, poll_interval_ms: int}
     */
    private function limits(): array
    {
        return [
            'max_body_bytes' => (int) config('webhooks.max_body_bytes', 64 * 1024),
            'max_requests_per_bin' => (int) config('webhooks.max_requests_per_bin', 50),
            'poll_interval_ms' => (int) config('webhooks.poll_interval_ms', 4000),
        ];
    }
}
