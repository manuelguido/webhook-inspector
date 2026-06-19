<script setup lang="ts">
import CopyButton from '@/components/webhooks/CopyButton.vue';
import type { InspectorLimits, WebhookBin } from '@/types/webhooks';

defineProps<{
    bin: WebhookBin;
    limits: InspectorLimits;
    requestCount: number;
}>();

const formatBytes = (bytes: number) => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};
</script>

<template>
    <section class="inspector-panel overflow-hidden">
        <div class="inspector-panel-header flex flex-col gap-4 p-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--inspector-accent)]">
                    Webhook endpoint
                </p>
                <h2 class="mt-1 text-base font-semibold text-[var(--inspector-fg)]">
                    Send test webhooks to this URL
                </h2>
            </div>
            <CopyButton :value="bin.webhook_url" label="Copy URL" copied-label="URL copied" />
        </div>

        <div class="p-4">
            <div
                class="inspector-code overflow-x-auto border border-[var(--inspector-border)] bg-[#0b1118] p-3 text-[var(--inspector-accent)]"
            >
                {{ bin.webhook_url }}
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="border border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-3">
                    <p class="text-[10px] uppercase tracking-[0.14em] text-[var(--inspector-faint)]">
                        Captured
                    </p>
                    <p class="mt-1 text-lg font-semibold text-[var(--inspector-fg)]">{{ requestCount }}</p>
                </div>
                <div class="border border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-3">
                    <p class="text-[10px] uppercase tracking-[0.14em] text-[var(--inspector-faint)]">
                        Retention
                    </p>
                    <p class="mt-1 text-lg font-semibold text-[var(--inspector-fg)]">
                        {{ limits.max_requests_per_bin }}
                    </p>
                </div>
                <div class="border border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-3">
                    <p class="text-[10px] uppercase tracking-[0.14em] text-[var(--inspector-faint)]">
                        Body limit
                    </p>
                    <p class="mt-1 text-lg font-semibold text-[var(--inspector-fg)]">
                        {{ formatBytes(limits.max_body_bytes) }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
