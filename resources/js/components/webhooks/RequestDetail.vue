<script setup lang="ts">
import { computed, ref, watch } from 'vue';

import BodyViewer from '@/components/webhooks/BodyViewer.vue';
import CopyButton from '@/components/webhooks/CopyButton.vue';
import EmptyState from '@/components/webhooks/EmptyState.vue';
import HeadersTable from '@/components/webhooks/HeadersTable.vue';
import RequestOverview from '@/components/webhooks/RequestOverview.vue';
import SegmentedTabs from '@/components/webhooks/ui/SegmentedTabs.vue';
import type { CapturedWebhookRequest } from '@/types/webhooks';

const props = withDefaults(
    defineProps<{
        request: CapturedWebhookRequest | null;
        webhookUrl?: string;
    }>(),
    {
        webhookUrl: '',
    },
);

const emit = defineEmits<{
    openQuickTest: [];
}>();

type Tab = 'Headers' | 'JSON' | 'Overview' | 'Query' | 'Raw';

const activeTab = ref<Tab>('Overview');

const availableTabs = computed<Tab[]>(() => {
    if (!props.request) {
        return [];
    }

    const supportedTabs: Tab[] = ['Overview', 'Headers', 'Query'];

    if (props.request.has_json_body) {
        supportedTabs.push('JSON');
    }

    if (props.request.raw_body) {
        supportedTabs.push('Raw');
    }

    return supportedTabs;
});

const activeTabId = computed(
    () =>
        `request-detail-${activeTab.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '')}`,
);

watch([() => props.request?.id, availableTabs], () => {
    if (!availableTabs.value.includes(activeTab.value)) {
        activeTab.value = 'Overview';
    }
});
</script>

<template>
    <section
        id="inspector-workspace"
        class="flex h-full min-h-[calc(100dvh-97px)] min-w-0 flex-col"
        tabindex="-1"
        aria-label="Selected request inspector"
    >
        <template v-if="request">
            <SegmentedTabs
                v-model="activeTab"
                id-prefix="request-detail"
                label="Request detail sections"
                :tabs="availableTabs"
            />

            <div
                :id="`${activeTabId}-panel`"
                class="min-h-0 flex-1 overflow-auto p-5"
                role="tabpanel"
                :aria-labelledby="activeTabId"
            >
                <RequestOverview
                    v-if="activeTab === 'Overview'"
                    :request="request"
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

                <BodyViewer
                    v-else-if="activeTab === 'JSON'"
                    :request="request"
                    mode="body"
                />

                <BodyViewer v-else :request="request" mode="raw" />
            </div>
        </template>

        <div
            v-else
            class="flex min-h-[520px] flex-1 items-center justify-center p-6"
        >
            <EmptyState
                bare
                title="Waiting for your first request"
                description="Send a request to the endpoint or run the example curl command. Incoming traffic appears automatically while polling is active."
            >
                <template #actions>
                    <CopyButton
                        v-if="webhookUrl"
                        :value="webhookUrl"
                        label="Copy endpoint"
                        copied-label="Endpoint copied"
                        variant="primary"
                    />
                    <button
                        type="button"
                        class="inspector-btn inspector-focus"
                        @click="emit('openQuickTest')"
                    >
                        Show curl
                    </button>
                </template>
            </EmptyState>
        </div>
    </section>
</template>
