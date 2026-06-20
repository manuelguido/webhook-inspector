<script setup lang="ts">
defineProps<{
    label?: string;
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="inspector-panel-header flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between"
    >
        <div class="min-w-0">
            <p v-if="label" class="inspector-section-label">
                {{ label }}
            </p>
            <h2
                class="mt-1 truncate text-base font-bold text-[var(--inspector-fg)]"
            >
                {{ title }}
            </h2>
            <p
                v-if="description"
                class="mt-1 max-w-2xl text-sm leading-6 font-light text-[var(--inspector-muted)]"
            >
                {{ description }}
            </p>
        </div>

        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
