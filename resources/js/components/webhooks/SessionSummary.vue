<script setup lang="ts">
import { computed } from 'vue';

import { formatBytes } from '@/lib/webhook-formatters';
import type { InspectorLimits, WebhookBin } from '@/types/webhooks';

const props = defineProps<{
    bin: WebhookBin;
    lastRefreshedLabel: string;
    limits: InspectorLimits;
    requestCount: number;
}>();

const rows = computed(() => [
    ['Captured', String(props.requestCount)],
    ['Retention', `${props.limits.max_requests_per_bin} requests`],
    ['Body limit', formatBytes(props.limits.max_body_bytes)],
    ['Polling', `${props.limits.poll_interval_ms / 1000}s`],
    ['Updated', props.lastRefreshedLabel],
]);
</script>

<template>
    <section class="grid gap-3" aria-labelledby="session-summary-title">
        <h2 id="session-summary-title" class="inspector-section-label">
            Session
        </h2>

        <dl class="grid gap-2">
            <div
                v-for="[label, value] in rows"
                :key="label"
                class="grid grid-cols-[92px_minmax(0,1fr)] items-baseline gap-3"
            >
                <dt class="text-xs font-light text-[var(--text-muted)]">
                    {{ label }}
                </dt>
                <dd
                    class="technical-value truncate text-right text-xs text-[var(--text-secondary)]"
                    :title="value"
                >
                    {{ value }}
                </dd>
            </div>
        </dl>

        <p
            v-if="bin.created_at"
            class="text-xs leading-5 font-light text-[var(--text-muted)]"
        >
            Created
            <span class="technical-value text-[var(--text-secondary)]">
                {{ new Date(bin.created_at).toLocaleString() }}
            </span>
        </p>
    </section>
</template>
