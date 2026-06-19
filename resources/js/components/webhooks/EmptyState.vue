<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div
        class="flex min-h-[240px] flex-col items-center justify-center border border-dashed border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-8 text-center"
    >
        <div
            class="mb-4 h-10 w-10 rounded-full border border-[rgba(125,211,252,0.25)] bg-[var(--inspector-accent-soft)]"
        />
        <h3 class="text-sm font-semibold text-[var(--inspector-fg)]">{{ title }}</h3>
        <p class="mt-2 max-w-md text-sm leading-6 text-[var(--inspector-muted)]">
            {{ description }}
        </p>
    </div>
</template>
