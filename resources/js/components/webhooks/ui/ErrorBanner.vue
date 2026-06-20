<script setup lang="ts">
defineProps<{
    message: string;
}>();

const emit = defineEmits<{
    retry: [];
}>();
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-[var(--inspector-radius-sm)] border border-[rgba(255,143,143,0.35)] bg-[var(--inspector-red-soft)] p-3 text-sm text-[var(--inspector-red)] sm:flex-row sm:items-center sm:justify-between"
        role="alert"
    >
        <p>{{ message }}</p>
        <button
            type="button"
            class="inspector-btn inspector-focus border-[rgba(255,143,143,0.35)] text-[var(--inspector-red)]"
            @click="emit('retry')"
        >
            Retry
        </button>
    </div>
</template>
