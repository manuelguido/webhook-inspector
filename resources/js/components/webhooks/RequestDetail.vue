<script setup lang="ts">
import { computed, ref, watch } from 'vue';

import BodyViewer from '@/components/webhooks/BodyViewer.vue';
import EmptyState from '@/components/webhooks/EmptyState.vue';
import HeadersTable from '@/components/webhooks/HeadersTable.vue';
import HttpMethodBadge from '@/components/webhooks/ui/HttpMethodBadge.vue';
import Panel from '@/components/webhooks/ui/Panel.vue';
import PanelHeader from '@/components/webhooks/ui/PanelHeader.vue';
import SegmentedTabs from '@/components/webhooks/ui/SegmentedTabs.vue';
import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
import type { CapturedWebhookRequest } from '@/types/webhooks';

const props = defineProps<{
    request: CapturedWebhookRequest | null;
}>();

type Tab = 'Headers' | 'Overview' | 'Parsed JSON' | 'Query' | 'Raw body';

const activeTab = ref<Tab>('Overview');

const availableTabs = computed<Tab[]>(() => {
    if (!props.request) {
        return [];
    }

    const supportedTabs: Tab[] = ['Overview'];

    if (props.request.has_json_body) {
        supportedTabs.push('Parsed JSON');
    }

    supportedTabs.push('Headers', 'Query');

    if (props.request.raw_body) {
        supportedTabs.push('Raw body');
    }

    return supportedTabs;
});

const metadata = computed(() => {
    if (!props.request) {
        return [];
    }

    return [
        ['Method', props.request.method],
        ['Content type', props.request.content_type || 'none'],
        ['Request size', formatBytes(props.request.body_size)],
        ['Captured at', new Date(props.request.captured_at).toLocaleString()],
        ['Source IP', props.request.ip_address || 'unavailable'],
        ['User agent', props.request.user_agent || 'unavailable'],
        ['Full URL', props.request.full_url],
    ];
});

const parseLabel = computed(() => {
    if (!props.request) {
        return '';
    }

    if (props.request.body_truncated) {
        return 'truncated';
    }

    if (props.request.has_json_body) {
        return 'parsed json';
    }

    if (props.request.raw_body) {
        return 'raw body';
    }

    return 'empty body';
});

const parseTone = computed(() => {
    if (!props.request) {
        return 'muted';
    }

    if (props.request.body_truncated) {
        return 'warning';
    }

    if (props.request.has_json_body) {
        return 'success';
    }

    return 'muted';
});

const activeTabId = computed(
    () =>
        `request-detail-${activeTab.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '')}`,
);

const formatBytes = (bytes: number) => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

watch([() => props.request?.id, availableTabs], () => {
    if (!availableTabs.value.includes(activeTab.value)) {
        activeTab.value = 'Overview';
    }
});
</script>

<template>
    <Panel panel-class="h-full">
        <PanelHeader
            label="Request detail"
            :title="request ? request.path : 'Waiting for traffic'"
            :description="
                request
                    ? 'Inspect metadata, headers, query parameters and payload content.'
                    : 'Select a captured request to inspect its metadata and payload.'
            "
        >
            <template v-if="request" #actions>
                <HttpMethodBadge :method="request.method" />
                <StatusBadge :tone="parseTone">{{ parseLabel }}</StatusBadge>
            </template>
        </PanelHeader>

        <div v-if="request" class="flex flex-1 flex-col">
            <SegmentedTabs
                v-model="activeTab"
                id-prefix="request-detail"
                label="Request detail sections"
                :tabs="availableTabs"
            />

            <div
                :id="`${activeTabId}-panel`"
                class="flex-1 overflow-auto p-4"
                role="tabpanel"
                :aria-labelledby="activeTabId"
            >
                <div v-if="activeTab === 'Overview'" class="grid gap-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="inspector-subtle-card p-3">
                            <p
                                class="text-xs font-light text-[var(--inspector-faint)]"
                            >
                                Request ID
                            </p>
                            <p
                                class="inspector-code mt-1 text-[var(--inspector-accent)]"
                            >
                                #{{ request.id }}
                            </p>
                        </div>
                        <div class="inspector-subtle-card p-3">
                            <p
                                class="text-xs font-light text-[var(--inspector-faint)]"
                            >
                                Payload state
                            </p>
                            <p
                                class="mt-1 text-sm font-bold text-[var(--inspector-fg)]"
                            >
                                {{ parseLabel }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-for="[label, value] in metadata"
                        :key="label"
                        class="grid gap-2 border-b border-[var(--inspector-border-soft)] py-2 last:border-b-0 md:grid-cols-[140px_minmax(0,1fr)]"
                    >
                        <p
                            class="text-sm font-light text-[var(--inspector-faint)]"
                        >
                            {{ label }}
                        </p>
                        <p
                            class="text-sm break-words text-[var(--inspector-muted)]"
                            :class="{
                                'inspector-code': [
                                    'Captured at',
                                    'Full URL',
                                ].includes(label),
                            }"
                        >
                            {{ value }}
                        </p>
                    </div>
                </div>

                <BodyViewer
                    v-else-if="activeTab === 'Parsed JSON'"
                    :request="request"
                    mode="body"
                />

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

                <BodyViewer v-else :request="request" mode="raw" />
            </div>
        </div>

        <div v-else class="p-4">
            <EmptyState
                compact
                title="No request selected"
                description="Captured requests appear in the list. Select one to inspect headers, query parameters, parsed JSON and the raw body."
            />
        </div>
    </Panel>
</template>
