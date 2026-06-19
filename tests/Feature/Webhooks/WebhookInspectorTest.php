<?php

namespace Tests\Feature\Webhooks;

use App\Models\CapturedRequest;
use App\Models\WebhookBin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WebhookInspectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_loads_and_creates_a_demo_bin(): void
    {
        $this->withoutVite();

        $response = $this->get(route('home'));

        $response->assertOk();
        $this->assertDatabaseCount('webhook_bins', 1);
        $this->assertTrue(WebhookBin::query()->first()?->token !== null);
    }

    public function test_current_bin_endpoint_returns_a_demo_bin(): void
    {
        $response = $this->getJson(route('inspector.bin'));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'bin' => ['token', 'webhook_url', 'requests_url', 'clear_url'],
                'requests',
                'limits',
            ]);

        $this->assertGreaterThanOrEqual(32, strlen((string) $response->json('bin.token')));
    }

    public function test_hook_captures_a_post_json_request(): void
    {
        $bin = WebhookBin::query()->create(['token' => 'test-token']);

        $response = $this->postJson(
            route('webhooks.capture', ['token' => $bin->token]).'?source=stripe',
            ['event' => 'invoice.paid', 'amount' => 129.9],
            ['X-Webhook-Signature' => 'sig_demo'],
        );

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('captured', true);

        $captured = CapturedRequest::query()->sole();

        $this->assertSame('POST', $captured->method);
        $this->assertSame(['source' => 'stripe'], $captured->query_params);
        $this->assertSame('invoice.paid', $captured->json_body['event']);
        $this->assertSame('sig_demo', $captured->headers['x-webhook-signature'][0] ?? null);
        $this->assertFalse($captured->body_truncated);
    }

    public function test_hook_stores_non_json_body_as_raw_text(): void
    {
        $bin = WebhookBin::query()->create(['token' => 'raw-token']);

        $response = $this->call(
            'POST',
            route('webhooks.capture', ['token' => $bin->token]),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            'plain webhook payload',
        );

        $response->assertOk();

        $captured = CapturedRequest::query()->sole();

        $this->assertSame('plain webhook payload', $captured->raw_body);
        $this->assertNull($captured->json_body);
        $this->assertSame('text/plain', $captured->content_type);
    }

    public function test_large_body_is_truncated(): void
    {
        config(['webhooks.max_body_bytes' => 10]);

        $bin = WebhookBin::query()->create(['token' => 'truncate-token']);

        $response = $this->call(
            'POST',
            route('webhooks.capture', ['token' => $bin->token]),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            str_repeat('x', 25),
        );

        $response->assertOk();

        $captured = CapturedRequest::query()->sole();

        $this->assertSame(25, $captured->body_size);
        $this->assertSame(str_repeat('x', 10), $captured->raw_body);
        $this->assertTrue($captured->body_truncated);
    }

    public function test_it_keeps_only_the_latest_requests_for_a_bin(): void
    {
        config(['webhooks.max_requests_per_bin' => 3]);

        $bin = WebhookBin::query()->create(['token' => 'prune-token']);

        foreach (range(0, 4) as $index) {
            $this->postJson(route('webhooks.capture', ['token' => $bin->token]), [
                'index' => $index,
            ])->assertOk();
        }

        $this->assertDatabaseCount('captured_requests', 3);

        $indexes = CapturedRequest::query()
            ->orderBy('id')
            ->get()
            ->map(fn (CapturedRequest $request): int => (int) $request->json_body['index'])
            ->all();

        $this->assertSame([2, 3, 4], $indexes);
    }

    public function test_clear_requests_removes_requests_for_the_bin(): void
    {
        $bin = WebhookBin::query()->create(['token' => 'clear-token']);

        $this->postJson(route('webhooks.capture', ['token' => $bin->token]), ['event' => 'first'])->assertOk();
        $this->postJson(route('webhooks.capture', ['token' => $bin->token]), ['event' => 'second'])->assertOk();

        $response = $this->deleteJson(route('inspector.requests.clear', ['bin' => $bin]));

        $response
            ->assertOk()
            ->assertJsonPath('requests', []);

        $this->assertDatabaseCount('captured_requests', 0);
    }

    public function test_invalid_token_returns_json_not_found_response(): void
    {
        $response = $this->postJson(route('webhooks.capture', ['token' => 'missing-token']), [
            'event' => 'ignored',
        ]);

        $response
            ->assertNotFound()
            ->assertJsonPath('ok', false)
            ->assertJsonPath('captured', false);

        $this->assertDatabaseCount('captured_requests', 0);
    }
}
