<script setup lang="ts">
withDefaults(
    defineProps<{
        description: string;
        title: string;
        bare?: boolean;
        compact?: boolean;
    }>(),
    {
        bare: false,
        compact: false,
    },
);
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-[var(--radius-md)] text-center"
        :class="[
            compact ? 'p-5' : 'p-8',
            bare
                ? 'bg-transparent'
                : 'border border-[var(--border-subtle)] bg-[var(--surface)]',
        ]"
    >
        <div
            class="mb-4 flex h-10 w-10 items-center justify-center rounded-[var(--radius-sm)] border border-[var(--border-subtle)] bg-[var(--accent-soft)] text-sm font-bold text-[var(--accent)]"
            aria-hidden="true"
        >
            —
        </div>
        <h3 class="text-base font-bold text-[var(--text-primary)]">
            {{ title }}
        </h3>
        <p
            class="mt-2 max-w-md text-sm leading-6 font-light text-[var(--text-secondary)]"
        >
            {{ description }}
        </p>
        <div
            v-if="$slots.actions"
            class="mt-4 flex flex-wrap justify-center gap-2"
        >
            <slot name="actions" />
        </div>
    </div>
</template>
