<script setup lang="ts">
import { computed } from 'vue';

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
            <h3 class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--inspector-faint)]">
                {{ label }}
            </h3>
            <span v-if="request.body_truncated" class="inspector-badge border-[rgba(244,198,118,0.35)] text-[var(--inspector-amber)]">
                truncated
            </span>
            <span v-if="request.has_json_body && mode === 'body'" class="inspector-badge border-[rgba(103,232,165,0.35)] text-[var(--inspector-green)]">
                parsed json
            </span>
        </div>

        <pre
            v-if="content"
            class="inspector-code max-h-[520px] overflow-auto border border-[var(--inspector-border)] bg-[#0b1118] p-4 text-[var(--inspector-muted)]"
        ><code>{{ content }}</code></pre>
        <p v-else class="border border-dashed border-[var(--inspector-border)] p-4 text-sm text-[var(--inspector-muted)]">
            This request did not include a body.
        </p>
    </div>
</template>
