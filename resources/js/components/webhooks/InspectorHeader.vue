<script setup lang="ts">
import { computed } from 'vue';

import HttpMethodBadge from '@/components/webhooks/ui/HttpMethodBadge.vue';
import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
import { formatClockTime, requestDisplayLabel } from '@/lib/webhook-formatters';
import type { CapturedWebhookRequest, InspectorLimits } from '@/types/webhooks';

const props = defineProps<{
    isClearing: boolean;
    isRefreshing: boolean;
    lastRefreshedLabel: string;
    limits: InspectorLimits;
    request: CapturedWebhookRequest | null;
    requestCount: number;
}>();

const emit = defineEmits<{
    clearRequests: [];
    refreshRequests: [];
    toggleSidebar: [];
}>();

const subtitle = computed(() => {
    if (!props.request) {
        return `${props.requestCount} captured for this endpoint`;
    }

    return `${requestDisplayLabel(props.request)} · ${formatClockTime(
        props.request.captured_at,
    )}`;
});
</script>

<template>
    <header
        class="flex flex-col gap-3 border-b border-[var(--border-subtle)] bg-[var(--workspace)] px-5 py-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div class="flex min-w-0 items-start gap-3">
            <button
                type="button"
                class="inspector-btn inspector-focus hidden max-[1100px]:inline-flex"
                aria-controls="context-sidebar"
                @click="emit('toggleSidebar')"
            >
                Requests
            </button>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <HttpMethodBadge v-if="request" :method="request.method" />
                    <p class="inspector-section-label">Inspector workspace</p>
                    <StatusBadge tone="success">Listening</StatusBadge>
                </div>
                <h2
                    class="mt-1 truncate text-xl font-bold tracking-normal text-[var(--text-primary)]"
                >
                    {{
                        request ? request.path : 'Waiting for incoming traffic'
                    }}
                </h2>
                <p
                    class="mt-1 truncate text-sm font-light text-[var(--text-secondary)]"
                    :title="subtitle"
                >
                    {{ subtitle }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:items-end">
            <div
                class="flex flex-wrap items-center gap-2 text-xs text-[var(--text-muted)]"
            >
                <span>Polling every</span>
                <span class="technical-value text-[var(--text-secondary)]">
                    {{ limits.poll_interval_ms / 1000 }}s
                </span>
                <span aria-hidden="true">·</span>
                <span>{{ lastRefreshedLabel }}</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="inspector-btn inspector-btn-primary inspector-focus"
                    :disabled="isRefreshing"
                    @click="emit('refreshRequests')"
                >
                    {{ isRefreshing ? 'Refreshing...' : 'Refresh' }}
                </button>
                <button
                    type="button"
                    class="inspector-btn inspector-btn-danger inspector-focus"
                    :disabled="isClearing || requestCount === 0"
                    @click="emit('clearRequests')"
                >
                    {{ isClearing ? 'Clearing...' : 'Clear' }}
                </button>
            </div>
        </div>
    </header>
</template>
