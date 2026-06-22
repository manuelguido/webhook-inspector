<script setup lang="ts">
import { computed } from 'vue';

import CodeBlock from '@/components/webhooks/ui/CodeBlock.vue';
import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
import type { CapturedWebhookRequest } from '@/types/webhooks';

const props = defineProps<{
    request: CapturedWebhookRequest;
    mode: 'body' | 'raw';
}>();

const content = computed(() => {
    if (props.mode === 'raw') {
        return props.request.raw_body || '';
    }

    if (props.request.has_json_body) {
        return JSON.stringify(props.request.json_body, null, 2);
    }

    return props.request.raw_body || '';
});

const label = computed(() => {
    if (props.mode === 'raw') {
        return 'Raw body';
    }

    return props.request.has_json_body ? 'Pretty JSON' : 'Body';
});
</script>

<template>
    <div>
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <h3 class="text-sm font-bold text-[var(--text-primary)]">
                {{ label }}
            </h3>
            <StatusBadge v-if="request.body_truncated" tone="warning">
                truncated
            </StatusBadge>
            <StatusBadge
                v-if="request.has_json_body && mode === 'body'"
                tone="success"
            >
                parsed json
            </StatusBadge>
        </div>

        <CodeBlock
            v-if="content"
            :value="content"
            :label="label"
            :language="
                request.has_json_body && mode === 'body' ? 'json' : 'text'
            "
            allow-wrap
            copyable
            copy-label="Copy body"
        />
        <p
            v-else
            class="rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--surface)] p-4 text-sm font-light text-[var(--text-secondary)]"
        >
            This request did not include a body.
        </p>
    </div>
</template>
