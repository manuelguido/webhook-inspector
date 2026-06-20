<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
    hint?: string;
}>();
</script>

<template>
    <div class="inspector-subtle-card p-3">
        <p class="text-xs font-light text-[var(--inspector-faint)]">
            {{ label }}
        </p>
        <p class="mt-1 text-xl font-bold text-[var(--inspector-fg)]">
            {{ value }}
        </p>
        <p
            v-if="hint"
            class="mt-1 text-xs font-light text-[var(--inspector-faint)]"
        >
            {{ hint }}
        </p>
    </div>
</template>
