<?php

namespace App\Actions\Webhooks;

use App\Models\CapturedRequest;
use App\Models\WebhookBin;
use Illuminate\Http\Request;

final readonly class CaptureWebhookRequest
{
    public function __construct(
        private PruneCapturedRequests $pruneCapturedRequests,
    ) {}

    public function __invoke(Request $request, WebhookBin $bin): CapturedRequest
    {
        $rawBody = $request->getContent();
        $bodySize = strlen($rawBody);
        $maxBodyBytes = max(1, (int) config('webhooks.max_body_bytes', 64 * 1024));
        $storedBody = $this->toUtf8Text(substr($rawBody, 0, $maxBodyBytes));
        $parsedJson = $this->parseJson($storedBody);

        $capturedRequest = CapturedRequest::query()->create([
            'webhook_bin_id' => $bin->id,
            'method' => $request->method(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl(),
            'headers' => $this->headers($request),
            'query_params' => $this->queryParams($request),
            'raw_body' => $storedBody === '' ? null : $storedBody,
            'json_body' => $parsedJson,
            'content_type' => $request->headers->get('content-type'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'body_size' => $bodySize,
            'body_truncated' => $bodySize > $maxBodyBytes,
            'captured_at' => now(),
        ]);

        $bin->forceFill([
            'last_captured_at' => $capturedRequest->captured_at,
        ])->save();

        ($this->pruneCapturedRequests)($bin);

        return $capturedRequest;
    }

    /**
     * @return array<string, list<string>>
     */
    private function headers(Request $request): array
    {
        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            $headers[$name] = array_map(
                fn (mixed $value): string => $this->stringify($value),
                $values,
            );
        }

        return $headers;
    }

    /**
     * @return array<string, mixed>
     */
    private function queryParams(Request $request): array
    {
        return $this->normalizeArray($request->query->all());
    }

    /**
     * @param  array<string|int, mixed>  $values
     * @return array<string, mixed>
     */
    private function normalizeArray(array $values): array
    {
        $normalized = [];

        foreach ($values as $key => $value) {
            $normalized[(string) $key] = is_array($value)
                ? $this->normalizeArray($value)
                : $this->normalizeValue($value);
        }

        return $normalized;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        return $this->stringify($value);
    }

    private function stringify(mixed $value): string
    {
        if (is_scalar($value)) {
            return (string) $value;
        }

        $json = json_encode($value);

        return $json === false ? '[unserializable]' : $json;
    }

    private function toUtf8Text(string $value): string
    {
        if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return $converted === false ? '' : $converted;
    }

    private function parseJson(string $body): mixed
    {
        if (trim($body) === '') {
            return null;
        }

        $decoded = json_decode($body, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
