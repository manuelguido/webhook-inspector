<script setup lang="ts">
import CopyButton from '@/components/webhooks/CopyButton.vue';
import EmptyState from '@/components/webhooks/EmptyState.vue';
import HttpMethodBadge from '@/components/webhooks/ui/HttpMethodBadge.vue';
import Panel from '@/components/webhooks/ui/Panel.vue';
import PanelHeader from '@/components/webhooks/ui/PanelHeader.vue';
import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
import type { CapturedWebhookRequest } from '@/types/webhooks';

withDefaults(
    defineProps<{
        isRefreshing?: boolean;
        requests: CapturedWebhookRequest[];
        selectedId: number | null;
        webhookUrl?: string;
    }>(),
    {
        isRefreshing: false,
        webhookUrl: '',
    },
);

const emit = defineEmits<{
    select: [request: CapturedWebhookRequest];
}>();

const formatTime = (value: string) =>
    new Intl.DateTimeFormat(undefined, {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(new Date(value));

const formatBytes = (bytes: number) => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    return `${(bytes / 1024).toFixed(1)} KB`;
};

const eventLabel = (request: CapturedWebhookRequest) => {
    if (
        request.json_body &&
        typeof request.json_body === 'object' &&
        !Array.isArray(request.json_body) &&
        'event' in request.json_body &&
        typeof request.json_body.event === 'string'
    ) {
        return request.json_body.event;
    }

    return request.content_type || 'no content type';
};

const parseState = (request: CapturedWebhookRequest) => {
    if (request.body_truncated) {
        return 'truncated';
    }

    if (request.has_json_body) {
        return 'json';
    }

    if (request.raw_body) {
        return 'raw';
    }

    return 'empty';
};

const stateTone = (request: CapturedWebhookRequest) => {
    if (request.body_truncated) {
        return 'warning';
    }

    if (request.has_json_body) {
        return 'success';
    }

    return 'muted';
};
</script>

<template>
    <Panel panel-class="h-full">
        <PanelHeader label="Incoming traffic" title="Captured requests">
            <template #actions>
                <StatusBadge tone="muted"
                    >{{ requests.length }} total</StatusBadge
                >
            </template>
        </PanelHeader>

        <div
            v-if="requests.length"
            class="max-h-[680px] overflow-auto"
            :aria-busy="isRefreshing"
        >
            <button
                v-for="request in requests"
                :key="request.id"
                type="button"
                class="inspector-focus block w-full border-b border-[var(--inspector-border-soft)] p-3 text-left transition hover:bg-[rgba(255,255,255,0.025)]"
                :aria-pressed="selectedId === request.id"
                :class="{
                    'border-l-2 border-l-[var(--inspector-accent)] bg-[var(--inspector-accent-soft)]':
                        selectedId === request.id,
                }"
                @click="emit('select', request)"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2">
                        <HttpMethodBadge :method="request.method" />
                        <div class="min-w-0">
                            <p
                                class="truncate text-sm font-bold text-[var(--inspector-fg)]"
                            >
                                {{ eventLabel(request) }}
                            </p>
                            <p
                                class="mt-1 truncate text-xs font-light text-[var(--inspector-faint)]"
                            >
                                {{ request.path }}
                            </p>
                        </div>
                    </div>
                    <StatusBadge
                        :tone="stateTone(request)"
                        badge-class="shrink-0"
                    >
                        {{ parseState(request) }}
                    </StatusBadge>
                </div>

                <div
                    class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-light text-[var(--inspector-faint)]"
                >
                    <span class="inspector-mono">#{{ request.id }}</span>
                    <span class="inspector-mono">{{
                        formatTime(request.captured_at)
                    }}</span>
                    <span>{{ formatBytes(request.body_size) }}</span>
                    <span v-if="selectedId === request.id" class="sr-only"
                        >selected</span
                    >
                </div>
            </button>
        </div>

        <div
            v-else-if="isRefreshing"
            class="grid gap-2 p-4"
            aria-label="Loading requests"
        >
            <div
                v-for="index in 3"
                :key="index"
                class="inspector-skeleton h-16 rounded-[var(--inspector-radius-sm)]"
            />
        </div>

        <div v-else class="p-4">
            <EmptyState
                compact
                title="No requests yet"
                description="Send a request to the endpoint above and it will appear here automatically."
            >
                <template v-if="webhookUrl" #actions>
                    <CopyButton
                        :value="webhookUrl"
                        label="Copy endpoint"
                        copied-label="Endpoint copied"
                    />
                </template>
            </EmptyState>
        </div>
    </Panel>
</template>
