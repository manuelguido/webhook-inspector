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
        return 'border-[var(--accent)] bg-[var(--accent-soft)] text-[var(--accent)]';
    }

    if (props.tone === 'success') {
        return 'border-[var(--success)] bg-[var(--success-soft)] text-[var(--success)]';
    }

    if (props.tone === 'warning') {
        return 'border-[var(--warning)] bg-[var(--warning-soft)] text-[var(--warning)]';
    }

    if (props.tone === 'danger') {
        return 'border-[var(--danger)] bg-[var(--danger-soft)] text-[var(--danger)]';
    }

    return 'border-[var(--border-default)] bg-[var(--surface)] text-[var(--text-secondary)]';
});
</script>

<template>
    <span :class="cn('inspector-badge', toneClass, badgeClass)">
        <slot />
    </span>
</template>
