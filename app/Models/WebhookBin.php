<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $token
 * @property Carbon|null $last_captured_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['token', 'last_captured_at'])]
class WebhookBin extends Model
{
    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /**
     * @return HasMany<CapturedRequest, $this>
     */
    public function capturedRequests(): HasMany
    {
        return $this->hasMany(CapturedRequest::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_captured_at' => 'datetime',
        ];
    }
}
