<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        idPrefix?: string;
        label: string;
        modelValue: string;
        tabs: readonly string[];
    }>(),
    {
        idPrefix: 'tabs',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const selectTab = (tab: string) => {
    emit('update:modelValue', tab);
};

const selectOffset = (current: string, offset: number) => {
    const currentIndex = props.tabs.indexOf(current);
    const nextIndex =
        (currentIndex + offset + props.tabs.length) % props.tabs.length;
    const nextTab = props.tabs[nextIndex];

    if (nextTab) {
        selectTab(nextTab);
    }
};

const safeId = (tab: string) =>
    tab
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
</script>

<template>
    <div
        class="flex flex-wrap gap-1 border-b border-[var(--inspector-border)] px-3 py-2"
        role="tablist"
        :aria-label="label"
    >
        <button
            v-for="tab in tabs"
            :id="`${idPrefix}-${safeId(tab)}`"
            :key="tab"
            type="button"
            role="tab"
            class="inspector-focus rounded-[var(--inspector-radius-sm)] px-3 py-2 text-sm font-normal text-[var(--inspector-muted)] transition hover:bg-[var(--inspector-surface-hover)] hover:text-[var(--inspector-fg)]"
            :aria-controls="`${idPrefix}-${safeId(tab)}-panel`"
            :aria-selected="modelValue === tab"
            :class="{
                'bg-[var(--inspector-accent-soft)] text-[var(--inspector-accent)]':
                    modelValue === tab,
            }"
            :tabindex="modelValue === tab ? 0 : -1"
            @click="selectTab(tab)"
            @keydown.left.prevent="selectOffset(tab, -1)"
            @keydown.right.prevent="selectOffset(tab, 1)"
        >
            {{ tab }}
        </button>
    </div>
</template>
