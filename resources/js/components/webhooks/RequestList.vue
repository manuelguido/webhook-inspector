<script setup lang="ts">
import CopyButton from '@/components/webhooks/CopyButton.vue';
import EmptyState from '@/components/webhooks/EmptyState.vue';
import HttpMethodBadge from '@/components/webhooks/ui/HttpMethodBadge.vue';
import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
import {
    formatBytes,
    formatClockTime,
    requestDisplayLabel,
    requestParseState,
    requestParseTone,
} from '@/lib/webhook-formatters';
import type { CapturedWebhookRequest } from '@/types/webhooks';

const props = withDefaults(
    defineProps<{
        isInitialLoading?: boolean;
        recentlyArrivedIds?: ReadonlySet<number>;
        requests: CapturedWebhookRequest[];
        selectedId: number | null;
        webhookUrl?: string;
    }>(),
    {
        isInitialLoading: false,
        recentlyArrivedIds: () => new Set<number>(),
        webhookUrl: '',
    },
);

const emit = defineEmits<{
    select: [request: CapturedWebhookRequest];
}>();

const selectOffset = (index: number, offset: number) => {
    const next = props.requests[index + offset];

    if (next) {
        emit('select', next);
    }
};
</script>

<template>
    <section class="flex min-h-0 flex-col" aria-labelledby="request-list-title">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 id="request-list-title" class="inspector-section-label">
                Requests
            </h2>
            <StatusBadge tone="muted">{{ requests.length }} total</StatusBadge>
        </div>

        <div
            v-if="requests.length"
            class="min-h-0 flex-1 overflow-auto rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--surface)]"
            :aria-busy="isInitialLoading"
            aria-label="Captured requests"
            role="listbox"
        >
            <button
                v-for="(request, index) in requests"
                :key="request.id"
                type="button"
                class="request-row inspector-focus block w-full border-b border-[var(--border-subtle)] px-3 py-3 text-left transition last:border-b-0 hover:bg-[var(--surface-hover)]"
                :class="{
                    'request-arrived': recentlyArrivedIds.has(request.id),
                }"
                role="option"
                :aria-selected="selectedId === request.id"
                @click="emit('select', request)"
                @keydown.down.prevent="selectOffset(index, 1)"
                @keydown.up.prevent="selectOffset(index, -1)"
            >
                <span class="flex items-start justify-between gap-3">
                    <span class="flex min-w-0 items-start gap-2">
                        <HttpMethodBadge :method="request.method" />
                        <span class="min-w-0">
                            <span
                                class="block truncate text-sm font-bold text-[var(--text-primary)]"
                            >
                                {{ requestDisplayLabel(request) }}
                            </span>
                            <span
                                class="request-url mt-1 block truncate text-xs text-[var(--text-muted)]"
                            >
                                {{ request.path }}
                            </span>
                        </span>
                    </span>
                    <span
                        class="technical-value shrink-0 text-xs text-[var(--text-muted)]"
                        :title="new Date(request.captured_at).toLocaleString()"
                    >
                        {{ formatClockTime(request.captured_at) }}
                    </span>
                </span>

                <span
                    class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-light text-[var(--text-muted)]"
                >
                    <span class="technical-value">#{{ request.id }}</span>
                    <span>{{ formatBytes(request.body_size) }}</span>
                    <span class="truncate">{{
                        request.content_type || 'no body'
                    }}</span>
                    <StatusBadge
                        :tone="requestParseTone(request)"
                        badge-class="py-0.5"
                    >
                        {{ requestParseState(request) }}
                    </StatusBadge>
                </span>

                <span v-if="selectedId === request.id" class="sr-only">
                    selected
                </span>
            </button>
        </div>

        <div
            v-else-if="isInitialLoading"
            class="grid gap-2 rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--surface)] p-3"
            aria-label="Loading requests"
        >
            <div
                v-for="index in 4"
                :key="index"
                class="inspector-skeleton h-16 rounded-[var(--radius-sm)]"
            />
        </div>

        <EmptyState
            v-else
            compact
            title="No requests yet"
            description="Send a request to the endpoint and it will appear here automatically."
        >
            <template v-if="webhookUrl" #actions>
                <CopyButton
                    :value="webhookUrl"
                    label="Copy endpoint"
                    copied-label="Endpoint copied"
                />
            </template>
        </EmptyState>
    </section>
</template>
