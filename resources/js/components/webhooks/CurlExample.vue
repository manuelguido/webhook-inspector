<script setup lang="ts">
import { computed } from 'vue';

import CopyButton from '@/components/webhooks/CopyButton.vue';
import CodeBlock from '@/components/webhooks/ui/CodeBlock.vue';
import { buildCurlCommand } from '@/lib/webhook-formatters';

const props = defineProps<{
    webhookUrl: string;
}>();

const curlCommand = computed(() => buildCurlCommand(props.webhookUrl));
</script>

<template>
    <div class="grid gap-4">
        <div
            class="flex flex-col gap-3 rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--sidebar)] p-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-[var(--text-primary)]">
                    Send a sample JSON webhook
                </h3>
                <p
                    class="mt-1 text-sm leading-6 font-light text-[var(--text-secondary)]"
                >
                    Paste this into a terminal to create a real captured request
                    without leaving the workbench.
                </p>
            </div>
            <CopyButton
                :value="curlCommand"
                label="Copy curl"
                copied-label="curl copied"
                variant="primary"
            />
        </div>

        <CodeBlock
            :value="curlCommand"
            label="Terminal command"
            copyable
            copy-label="Copy curl"
        />
    </div>
</template>
