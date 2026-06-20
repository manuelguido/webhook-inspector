<script setup lang="ts">
import CopyButton from '@/components/webhooks/CopyButton.vue';

withDefaults(
    defineProps<{
        value: string;
        label?: string;
        copyLabel?: string;
        copyable?: boolean;
        tone?: 'accent' | 'default';
    }>(),
    {
        label: '',
        copyLabel: 'Copy',
        copyable: false,
        tone: 'default',
    },
);
</script>

<template>
    <div
        class="overflow-hidden rounded-[var(--inspector-radius-sm)] border border-[var(--inspector-border)] bg-[var(--inspector-code-bg)]"
    >
        <div
            v-if="label || copyable"
            class="flex items-center justify-between gap-3 border-b border-[var(--inspector-border-soft)] px-3 py-2"
        >
            <p
                v-if="label"
                class="text-xs font-light text-[var(--inspector-faint)]"
            >
                {{ label }}
            </p>
            <CopyButton
                v-if="copyable"
                :value="value"
                :label="copyLabel"
                copied-label="Copied"
                variant="tertiary"
            />
        </div>

        <pre
            class="inspector-code max-h-[520px] overflow-auto p-3"
            :class="
                tone === 'accent'
                    ? 'text-[var(--inspector-accent)]'
                    : 'text-[var(--inspector-muted)]'
            "
        ><code>{{ value }}</code></pre>
    </div>
</template>
