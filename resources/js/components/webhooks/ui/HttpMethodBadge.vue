<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    method: string;
}>();

const methodClass = computed(() => {
    const normalized = props.method.toUpperCase();

    if (normalized === 'GET') {
        return 'border-[rgba(125,211,252,0.45)] bg-[rgba(125,211,252,0.1)] text-[var(--inspector-accent)]';
    }

    if (normalized === 'POST') {
        return 'border-[rgba(116,231,164,0.42)] bg-[var(--inspector-green-soft)] text-[var(--inspector-green)]';
    }

    if (['PATCH', 'PUT'].includes(normalized)) {
        return 'border-[rgba(247,198,111,0.42)] bg-[var(--inspector-amber-soft)] text-[var(--inspector-amber)]';
    }

    return 'border-[rgba(255,143,143,0.38)] bg-[var(--inspector-red-soft)] text-[var(--inspector-red)]';
});
</script>

<template>
    <span
        class="inspector-badge inspector-mono min-w-14 justify-center font-bold"
        :class="methodClass"
    >
        {{ method.toUpperCase() }}
    </span>
</template>
