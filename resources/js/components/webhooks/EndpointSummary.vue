<script setup lang="ts">
import { computed } from 'vue';

import CopyButton from '@/components/webhooks/CopyButton.vue';
import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
import { buildCurlCommand } from '@/lib/webhook-formatters';
import type { WebhookBin } from '@/types/webhooks';

const props = defineProps<{
    bin: WebhookBin;
    requestCount: number;
}>();

const emit = defineEmits<{
    openQuickTest: [];
}>();

const curlCommand = computed(() => buildCurlCommand(props.bin.webhook_url));
</script>

<template>
    <section
        id="endpoint-summary"
        class="grid gap-3"
        tabindex="-1"
        aria-labelledby="endpoint-summary-title"
    >
        <div class="flex items-center justify-between gap-3">
            <h2 id="endpoint-summary-title" class="inspector-section-label">
                Endpoint
            </h2>
            <StatusBadge tone="success">Ready</StatusBadge>
        </div>

        <div
            class="rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--surface)] p-3"
        >
            <p
                class="request-url text-sm leading-6 break-all text-[var(--text-primary)]"
                :title="bin.webhook_url"
            >
                {{ bin.webhook_url }}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <CopyButton
                    :value="bin.webhook_url"
                    label="Copy endpoint"
                    copied-label="Endpoint copied"
                    variant="primary"
                />
                <CopyButton
                    :value="curlCommand"
                    label="Copy curl"
                    copied-label="curl copied"
                    variant="secondary"
                />
                <button
                    type="button"
                    class="inspector-btn inspector-focus"
                    @click="emit('openQuickTest')"
                >
                    Show
                </button>
            </div>
        </div>

        <p class="text-xs leading-5 font-light text-[var(--text-muted)]">
            Incoming GET, POST, PUT, PATCH and DELETE requests are captured for
            this endpoint. Current session:
            <span class="technical-value text-[var(--text-secondary)]">
                {{ requestCount }}
            </span>
            {{ requestCount === 1 ? 'request' : 'requests' }}.
        </p>
    </section>
</template>
