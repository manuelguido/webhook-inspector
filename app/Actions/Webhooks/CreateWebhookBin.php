<?php

namespace App\Actions\Webhooks;

use App\Models\WebhookBin;
use Illuminate\Support\Str;

final class CreateWebhookBin
{
    public function __invoke(): WebhookBin
    {
        $length = max(32, (int) config('webhooks.token_length', 48));

        do {
            $token = Str::random($length);
        } while (WebhookBin::query()->where('token', $token)->exists());

        return WebhookBin::query()->create([
            'token' => $token,
        ]);
    }
}
