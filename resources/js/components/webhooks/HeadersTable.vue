<script setup lang="ts">
import { computed } from 'vue';

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
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--inspector-faint)]">
            {{ title }}
        </h3>

        <div v-if="rows.length" class="overflow-hidden border border-[var(--inspector-border)]">
            <div
                v-for="row in rows"
                :key="row.key"
                class="grid gap-3 border-b border-[var(--inspector-border)] p-3 last:border-b-0 md:grid-cols-[220px_minmax(0,1fr)]"
            >
                <div class="inspector-code break-all text-[var(--inspector-accent)]">{{ row.key }}</div>
                <pre class="inspector-code whitespace-pre-wrap break-words text-[var(--inspector-muted)]">{{ row.value }}</pre>
            </div>
        </div>

        <p v-else class="border border-dashed border-[var(--inspector-border)] p-4 text-sm text-[var(--inspector-muted)]">
            {{ emptyMessage }}
        </p>
    </div>
</template>
