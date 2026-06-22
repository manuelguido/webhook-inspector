<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    method: string;
}>();

const methodClass = computed(() => {
    const normalized = props.method.toUpperCase();

    if (normalized === 'GET') {
        return 'method-badge-get';
    }

    if (normalized === 'POST') {
        return 'method-badge-post';
    }

    if (normalized === 'PUT') {
        return 'method-badge-put';
    }

    if (normalized === 'PATCH') {
        return 'method-badge-patch';
    }

    return 'method-badge-delete';
});
</script>

<template>
    <span
        class="method-badge inspector-badge inspector-mono min-w-14 justify-center font-bold"
        :class="methodClass"
    >
        {{ method.toUpperCase() }}
    </span>
</template>
