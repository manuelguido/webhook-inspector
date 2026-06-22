<script setup lang="ts">
import { computed } from 'vue';

import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
import {
    formatBytes,
    formatDateTime,
    requestParseState,
    requestParseTone,
} from '@/lib/webhook-formatters';
import type { CapturedWebhookRequest } from '@/types/webhooks';

const props = defineProps<{
    request: CapturedWebhookRequest;
}>();

const rows = computed(() => [
    ['Method', props.request.method],
    ['Path', props.request.path],
    ['Content-Type', props.request.content_type || 'none'],
    ['Payload size', formatBytes(props.request.body_size)],
    ['Received at', formatDateTime(props.request.captured_at)],
    ['Source IP', props.request.ip_address || 'unavailable'],
    ['User agent', props.request.user_agent || 'unavailable'],
    ['Full URL', props.request.full_url],
    ['Headers', String(Object.keys(props.request.headers).length)],
    ['Query params', String(Object.keys(props.request.query_params).length)],
]);
</script>

<template>
    <div class="grid gap-5">
        <div
            class="flex flex-wrap items-center gap-2 rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--surface)] p-3"
        >
            <StatusBadge :tone="requestParseTone(request)">
                {{ requestParseState(request) }}
            </StatusBadge>
            <StatusBadge v-if="request.body_truncated" tone="warning">
                body truncated
            </StatusBadge>
            <span class="text-sm font-light text-[var(--text-secondary)]">
                Request
                <span class="technical-value">#{{ request.id }}</span>
            </span>
        </div>

        <dl
            class="overflow-hidden rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--surface)]"
        >
            <div
                v-for="[label, value] in rows"
                :key="label"
                class="grid gap-2 border-b border-[var(--border-subtle)] px-4 py-3 last:border-b-0 md:grid-cols-[160px_minmax(0,1fr)]"
            >
                <dt class="text-sm font-light text-[var(--text-muted)]">
                    {{ label }}
                </dt>
                <dd
                    class="text-sm break-words text-[var(--text-secondary)]"
                    :class="{
                        'technical-value': [
                            'Method',
                            'Payload size',
                            'Received at',
                            'Source IP',
                            'Full URL',
                            'Headers',
                            'Query params',
                        ].includes(label),
                    }"
                >
                    {{ value }}
                </dd>
            </div>
        </dl>
    </div>
</template>
