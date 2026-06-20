<script setup lang="ts">
import CopyButton from '@/components/webhooks/CopyButton.vue';
import CodeBlock from '@/components/webhooks/ui/CodeBlock.vue';
import MetricCard from '@/components/webhooks/ui/MetricCard.vue';
import Panel from '@/components/webhooks/ui/Panel.vue';
import PanelHeader from '@/components/webhooks/ui/PanelHeader.vue';
import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
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
    <Panel panel-class="border-[var(--inspector-border-strong)]">
        <PanelHeader
            label="Webhook endpoint"
            title="Send requests to this URL"
            description="This public endpoint accepts GET, POST, PUT, PATCH and DELETE requests for the current demo session."
        >
            <template #actions>
                <StatusBadge tone="success">Ready</StatusBadge>
                <CopyButton
                    :value="bin.webhook_url"
                    label="Copy URL"
                    copied-label="URL copied"
                    variant="primary"
                />
            </template>
        </PanelHeader>

        <div class="p-4">
            <CodeBlock :value="bin.webhook_url" tone="accent" />

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <MetricCard
                    label="Captured"
                    :value="requestCount"
                    hint="visible in this session"
                />
                <MetricCard
                    label="Retention"
                    :value="limits.max_requests_per_bin"
                    hint="latest requests"
                />
                <MetricCard
                    label="Body limit"
                    :value="formatBytes(limits.max_body_bytes)"
                    hint="stored per request"
                />
            </div>
        </div>
    </Panel>
</template>
