<script setup lang="ts">
import { computed } from 'vue';

import CopyButton from '@/components/webhooks/CopyButton.vue';

const props = defineProps<{
    title: string;
    items: Record<string, unknown>;
    emptyMessage: string;
}>();

const stringify = (value: unknown): string => {
    if (Array.isArray(value)) {
        return value.map((item) => stringify(item)).join(', ');
    }

    if (value === null) {
        return 'null';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
    }

    return String(value);
};

const rows = computed(() =>
    Object.entries(props.items).map(([key, value]) => ({
        key,
        value: stringify(value),
    })),
);
</script>

<template>
    <div>
        <h3 class="mb-3 text-sm font-bold text-[var(--text-primary)]">
            {{ title }}
        </h3>

        <div
            v-if="rows.length"
            class="overflow-hidden rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--surface)]"
        >
            <div
                v-for="row in rows"
                :key="row.key"
                class="grid gap-3 border-b border-[var(--border-subtle)] p-3 last:border-b-0 md:grid-cols-[220px_minmax(0,1fr)_auto]"
            >
                <div
                    class="technical-value text-sm font-bold break-all text-[var(--accent)]"
                >
                    {{ row.key }}
                </div>
                <pre
                    class="code-content text-sm break-words whitespace-pre-wrap text-[var(--text-secondary)]"
                    >{{ row.value }}</pre
                >
                <CopyButton
                    :value="row.value"
                    label="Copy"
                    copied-label="Copied"
                    variant="tertiary"
                />
            </div>
        </div>

        <p
            v-else
            class="rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--surface)] p-4 text-sm font-light text-[var(--text-secondary)]"
        >
            {{ emptyMessage }}
        </p>
    </div>
</template>
