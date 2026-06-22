<script setup lang="ts">
import EndpointSummary from '@/components/webhooks/EndpointSummary.vue';
import RequestList from '@/components/webhooks/RequestList.vue';
import SessionSummary from '@/components/webhooks/SessionSummary.vue';
import type {
    CapturedWebhookRequest,
    InspectorLimits,
    WebhookBin,
} from '@/types/webhooks';

defineProps<{
    bin: WebhookBin;
    isInitialLoading: boolean;
    lastRefreshedLabel: string;
    limits: InspectorLimits;
    recentlyArrivedIds: ReadonlySet<number>;
    requestCount: number;
    requests: CapturedWebhookRequest[];
    selectedId: number | null;
}>();

const emit = defineEmits<{
    close: [];
    openQuickTest: [];
    select: [request: CapturedWebhookRequest];
}>();
</script>

<template>
    <div class="flex h-dvh flex-col max-[700px]:h-[calc(100dvh-56px)]">
        <header
            class="flex items-start justify-between gap-3 border-b border-[var(--border-subtle)] px-4 py-4"
        >
            <div class="min-w-0">
                <p class="inspector-section-label">Workbench</p>
                <h1
                    class="mt-1 text-lg font-bold tracking-normal text-[var(--text-primary)]"
                >
                    Webhook Inspector
                </h1>
                <p class="mt-1 text-xs font-light text-[var(--text-muted)]">
                    Request inbox and endpoint context.
                </p>
            </div>

            <button
                type="button"
                class="inspector-focus hidden rounded-[var(--radius-sm)] px-2 py-1 text-sm text-[var(--text-muted)] hover:bg-[var(--surface-hover)] max-[1100px]:block"
                aria-label="Close request sidebar"
                @click="emit('close')"
            >
                Close
            </button>
        </header>

        <div class="flex-1 overflow-hidden">
            <div class="flex h-full flex-col gap-5 overflow-auto px-4 py-4">
                <EndpointSummary
                    :bin="bin"
                    :request-count="requestCount"
                    @open-quick-test="emit('openQuickTest')"
                />

                <SessionSummary
                    :bin="bin"
                    :last-refreshed-label="lastRefreshedLabel"
                    :limits="limits"
                    :request-count="requestCount"
                />

                <RequestList
                    class="min-h-0 flex-1"
                    :is-initial-loading="isInitialLoading"
                    :recently-arrived-ids="recentlyArrivedIds"
                    :requests="requests"
                    :selected-id="selectedId"
                    :webhook-url="bin.webhook_url"
                    @select="emit('select', $event)"
                />
            </div>
        </div>
    </div>
</template>
