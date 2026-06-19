<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $webhook_bin_id
 * @property string $method
 * @property string $path
 * @property string $full_url
 * @property array<string, list<string>> $headers
 * @property array<string, mixed> $query_params
 * @property string|null $raw_body
 * @property mixed $json_body
 * @property string|null $content_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $body_size
 * @property bool $body_truncated
 * @property Carbon $captured_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'webhook_bin_id',
    'method',
    'path',
    'full_url',
    'headers',
    'query_params',
    'raw_body',
    'json_body',
    'content_type',
    'ip_address',
    'user_agent',
    'body_size',
    'body_truncated',
    'captured_at',
])]
class CapturedRequest extends Model
{
    /**
     * @return BelongsTo<WebhookBin, $this>
     */
    public function webhookBin(): BelongsTo
    {
        return $this->belongsTo(WebhookBin::class);
    }

    /**
     * @return array{
     *     id: int,
     *     method: string,
     *     path: string,
     *     full_url: string,
     *     headers: array<string, list<string>>,
     *     query_params: array<string, mixed>,
     *     raw_body: string|null,
     *     json_body: mixed,
     *     has_json_body: bool,
     *     content_type: string|null,
     *     ip_address: string|null,
     *     user_agent: string|null,
     *     body_size: int,
     *     body_truncated: bool,
     *     captured_at: string
     * }
     */
    public function toInspectorArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'path' => $this->path,
            'full_url' => $this->full_url,
            'headers' => $this->headers,
            'query_params' => $this->query_params,
            'raw_body' => $this->raw_body,
            'json_body' => $this->json_body,
            'has_json_body' => $this->json_body !== null,
            'content_type' => $this->content_type,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'body_size' => $this->body_size,
            'body_truncated' => $this->body_truncated,
            'captured_at' => $this->captured_at->toIso8601String(),
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'query_params' => 'array',
            'json_body' => 'array',
            'body_truncated' => 'boolean',
            'captured_at' => 'datetime',
        ];
    }
}
