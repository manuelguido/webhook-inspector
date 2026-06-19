<?php

namespace App\Actions\Webhooks;

use App\Models\WebhookBin;

final class PruneCapturedRequests
{
    public function __invoke(WebhookBin $bin): void
    {
        $maxRequests = max(1, (int) config('webhooks.max_requests_per_bin', 50));

        $idsToKeep = $bin->capturedRequests()
            ->latest('captured_at')
            ->latest('id')
            ->limit($maxRequests)
            ->pluck('id')
            ->all();

        if ($idsToKeep === []) {
            return;
        }

        $bin->capturedRequests()
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }
}
