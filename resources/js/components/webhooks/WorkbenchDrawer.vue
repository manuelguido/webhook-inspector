<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

const props = withDefaults(
    defineProps<{
        description?: string;
        open: boolean;
        title: string;
    }>(),
    {
        description: '',
    },
);

const emit = defineEmits<{
    close: [];
}>();

const closeButton = ref<HTMLButtonElement | null>(null);
const titleId = computed(
    () =>
        `${props.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '')}-drawer-title`,
);

const close = () => {
    emit('close');
};

const onKeydown = (event: KeyboardEvent) => {
    if (props.open && event.key === 'Escape') {
        close();
    }
};

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            await nextTick();
            closeButton.value?.focus();
        }
    },
);

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex justify-end"
            role="presentation"
        >
            <button
                type="button"
                class="workbench-drawer-backdrop absolute inset-0 cursor-default"
                aria-label="Close panel"
                @click="close"
            />

            <section
                class="relative flex h-full w-full max-w-xl flex-col border-l border-[var(--border-subtle)] bg-[var(--surface)] shadow-2xl sm:w-[560px]"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="titleId"
            >
                <header
                    class="flex items-start justify-between gap-4 border-b border-[var(--border-subtle)] px-5 py-4"
                >
                    <div class="min-w-0">
                        <p class="inspector-section-label">Panel</p>
                        <h2
                            :id="titleId"
                            class="mt-1 text-lg font-bold text-[var(--text-primary)]"
                        >
                            {{ title }}
                        </h2>
                        <p
                            v-if="description"
                            class="mt-1 text-sm font-light text-[var(--text-secondary)]"
                        >
                            {{ description }}
                        </p>
                    </div>
                    <button
                        ref="closeButton"
                        type="button"
                        class="inspector-btn inspector-focus"
                        @click="close"
                    >
                        Close
                    </button>
                </header>

                <div class="flex-1 overflow-auto p-5">
                    <slot />
                </div>
            </section>
        </div>
    </Teleport>
</template>
