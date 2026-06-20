<script setup lang="ts">
import { computed, ref } from 'vue';

import CopyButton from '@/components/webhooks/CopyButton.vue';
import CodeBlock from '@/components/webhooks/ui/CodeBlock.vue';
import Panel from '@/components/webhooks/ui/Panel.vue';
import PanelHeader from '@/components/webhooks/ui/PanelHeader.vue';

const props = defineProps<{
    webhookUrl: string;
}>();

const isOpen = ref(false);

const curlCommand = computed(
    () => `curl -X POST "${props.webhookUrl}?source=portfolio" \\
  -H "Content-Type: application/json" \\
  -H "X-Demo-Signature: whsec_demo_123" \\
  -d '{
    "event": "invoice.paid",
    "id": "evt_demo_001",
    "created_at": "2026-06-17T18:42:00Z",
    "data": {
      "invoice_id": "inv_2026_0042",
      "customer": "ada@example.com",
      "amount": 129.90,
      "currency": "USD"
    }
  }'`,
);
</script>

<template>
    <Panel>
        <PanelHeader
            label="Quick test"
            title="Example curl request"
            description="Keep a ready-made request close by without letting it dominate the inspector."
        >
            <template #actions>
                <CopyButton
                    :value="curlCommand"
                    label="Copy curl"
                    copied-label="curl copied"
                />
                <button
                    type="button"
                    class="inspector-btn inspector-focus"
                    :aria-expanded="isOpen"
                    aria-controls="curl-example"
                    @click="isOpen = !isOpen"
                >
                    {{ isOpen ? 'Hide example' : 'Show example' }}
                </button>
            </template>
        </PanelHeader>

        <div v-if="isOpen" id="curl-example" class="p-4">
            <CodeBlock
                :value="curlCommand"
                label="Terminal command"
                copyable
                copy-label="Copy curl"
            />
        </div>
    </Panel>
</template>
