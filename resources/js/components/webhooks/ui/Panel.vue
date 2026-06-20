<script setup lang="ts">
import { computed } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        as?: 'article' | 'div' | 'section';
        panelClass?: string;
    }>(),
    {
        as: 'section',
        panelClass: '',
    },
);

const component = computed(() => props.as);
</script>

<template>
    <component
        :is="component"
        :class="cn('inspector-panel overflow-hidden', panelClass)"
    >
        <slot />
    </component>
</template>
