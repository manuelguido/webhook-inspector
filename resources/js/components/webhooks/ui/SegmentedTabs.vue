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
        class="flex flex-wrap gap-1 border-b border-[var(--border-subtle)] px-5"
        role="tablist"
        :aria-label="label"
    >
        <button
            v-for="tab in tabs"
            :id="`${idPrefix}-${safeId(tab)}`"
            :key="tab"
            type="button"
            role="tab"
            class="inspector-focus border-b-2 border-transparent px-3 py-3 text-sm font-normal text-[var(--text-secondary)] transition hover:text-[var(--text-primary)]"
            :aria-controls="`${idPrefix}-${safeId(tab)}-panel`"
            :aria-selected="modelValue === tab"
            :class="{
                'border-[var(--accent)] text-[var(--accent)]':
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
