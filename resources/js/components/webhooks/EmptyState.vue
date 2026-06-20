<script setup lang="ts">
withDefaults(
    defineProps<{
        description: string;
        title: string;
        compact?: boolean;
    }>(),
    {
        compact: false,
    },
);
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-[var(--inspector-radius-sm)] border border-dashed border-[var(--inspector-border)] bg-[rgba(255,255,255,0.018)] text-center"
        :class="compact ? 'p-5' : 'p-8'"
    >
        <div
            class="mb-4 flex h-10 w-10 items-center justify-center rounded-full border border-[rgba(125,211,252,0.25)] bg-[var(--inspector-accent-soft)] text-sm font-bold text-[var(--inspector-accent)]"
            aria-hidden="true"
        >
            —
        </div>
        <h3 class="text-base font-bold text-[var(--inspector-fg)]">
            {{ title }}
        </h3>
        <p
            class="mt-2 max-w-md text-sm leading-6 font-light text-[var(--inspector-muted)]"
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
