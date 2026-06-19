<script setup lang="ts">
import { computed } from 'vue';

import CopyButton from '@/components/webhooks/CopyButton.vue';

const props = defineProps<{
    webhookUrl: string;
}>();

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
    <section class="inspector-panel overflow-hidden">
        <div class="inspector-panel-header flex items-center justify-between gap-3 p-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--inspector-green)]">
                    Quick test
                </p>
                <h2 class="mt-1 text-base font-semibold text-[var(--inspector-fg)]">Example curl request</h2>
            </div>
            <CopyButton :value="curlCommand" label="Copy curl" copied-label="curl copied" />
        </div>

        <pre
            class="inspector-code max-h-[260px] overflow-auto bg-[#0b1118] p-4 text-[var(--inspector-muted)]"
        ><code>{{ curlCommand }}</code></pre>
    </section>
</template>
