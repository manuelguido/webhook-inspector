<script setup lang="ts">
import { computed } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        tone?: 'accent' | 'danger' | 'muted' | 'success' | 'warning';
        badgeClass?: string;
    }>(),
    {
        tone: 'muted',
        badgeClass: '',
    },
);

const toneClass = computed(() => {
    if (props.tone === 'accent') {
        return 'border-[rgba(125,211,252,0.35)] bg-[var(--inspector-accent-soft)] text-[var(--inspector-accent)]';
    }

    if (props.tone === 'success') {
        return 'border-[rgba(116,231,164,0.35)] bg-[var(--inspector-green-soft)] text-[var(--inspector-green)]';
    }

    if (props.tone === 'warning') {
        return 'border-[rgba(247,198,111,0.38)] bg-[var(--inspector-amber-soft)] text-[var(--inspector-amber)]';
    }

    if (props.tone === 'danger') {
        return 'border-[rgba(255,143,143,0.36)] bg-[var(--inspector-red-soft)] text-[var(--inspector-red)]';
    }

    return 'border-[var(--inspector-border)] bg-[rgba(255,255,255,0.025)] text-[var(--inspector-muted)]';
});
</script>

<template>
    <span :class="cn('inspector-badge', toneClass, badgeClass)">
        <slot />
    </span>
</template>
