<script setup lang="ts">
import { computed, ref, watch } from 'vue';

import BodyViewer from '@/components/webhooks/BodyViewer.vue';
import EmptyState from '@/components/webhooks/EmptyState.vue';
import HeadersTable from '@/components/webhooks/HeadersTable.vue';
import type { CapturedWebhookRequest } from '@/types/webhooks';

const props = defineProps<{
    request: CapturedWebhookRequest | null;
}>();

const tabs = ['Overview', 'Headers', 'Query', 'Body', 'Raw'] as const;
type Tab = (typeof tabs)[number];

const activeTab = ref<Tab>('Overview');

watch(
    () => props.request?.id,
    () => {
        activeTab.value = 'Overview';
    },
);

const metadata = computed(() => {
    if (!props.request) {
        return [];
    }

    return [
        ['Method', props.request.method],
        ['Path', props.request.path],
        ['Full URL', props.request.full_url],
        ['Content type', props.request.content_type || 'none'],
        ['IP address', props.request.ip_address || 'unavailable'],
        ['User agent', props.request.user_agent || 'unavailable'],
        ['Body size', `${props.request.body_size} bytes`],
        ['Captured at', new Date(props.request.captured_at).toLocaleString()],
    ];
});
</script>

<template>
    <section class="inspector-panel flex min-h-[520px] flex-col overflow-hidden">
        <div class="inspector-panel-header flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--inspector-accent)]">
                    Request detail
                </p>
                <h2 class="mt-1 text-base font-semibold text-[var(--inspector-fg)]">
                    {{ request ? `${request.method} ${request.path}` : 'Waiting for traffic' }}
                </h2>
            </div>

            <span v-if="request" class="inspector-badge border-[rgba(103,232,165,0.35)] text-[var(--inspector-green)]">
                captured
            </span>
        </div>

        <div v-if="request" class="flex flex-1 flex-col">
            <div class="flex gap-1 overflow-x-auto border-b border-[var(--inspector-border)] px-4 py-3">
                <button
                    v-for="tab in tabs"
                    :key="tab"
                    type="button"
                    class="inspector-focus rounded-[6px] px-3 py-1.5 text-xs font-semibold text-[var(--inspector-muted)] transition hover:bg-[var(--inspector-panel-2)] hover:text-[var(--inspector-fg)]"
                    :class="{ 'bg-[var(--inspector-accent-soft)] text-[var(--inspector-accent)]': activeTab === tab }"
                    @click="activeTab = tab"
                >
                    {{ tab }}
                </button>
            </div>

            <div class="flex-1 overflow-auto p-4">
                <div v-if="activeTab === 'Overview'" class="grid gap-3">
                    <div
                        v-for="[label, value] in metadata"
                        :key="label"
                        class="grid gap-2 border border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-3 md:grid-cols-[160px_minmax(0,1fr)]"
                    >
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--inspector-faint)]">
                            {{ label }}
                        </p>
                        <p class="inspector-code break-words text-[var(--inspector-muted)]">{{ value }}</p>
                    </div>
                </div>

                <HeadersTable
                    v-else-if="activeTab === 'Headers'"
                    title="Headers"
                    :items="request.headers"
                    empty-message="No headers were captured."
                />

                <HeadersTable
                    v-else-if="activeTab === 'Query'"
                    title="Query parameters"
                    :items="request.query_params"
                    empty-message="No query parameters were sent."
                />

                <BodyViewer v-else-if="activeTab === 'Body'" :request="request" mode="body" />
                <BodyViewer v-else :request="request" mode="raw" />
            </div>
        </div>

        <div v-else class="p-4">
            <EmptyState
                title="No request selected"
                description="Captured webhooks will appear in the list. Select one to inspect headers, query params, parsed JSON and the raw body."
            />
        </div>
    </section>
</template>
