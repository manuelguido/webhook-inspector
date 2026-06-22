<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        value: string;
        label?: string;
        copiedLabel?: string;
        disabled?: boolean;
        variant?: 'primary' | 'secondary' | 'tertiary';
    }>(),
    {
        label: 'Copy',
        copiedLabel: 'Copied',
        disabled: false,
        variant: 'secondary',
    },
);

const copied = ref(false);
const error = ref('');
let resetTimer: number | undefined;

const buttonClass = computed(() =>
    cn(
        'inspector-btn inspector-focus',
        props.variant === 'primary' && 'inspector-btn-primary',
        props.variant === 'secondary' && 'inspector-btn-secondary',
        props.variant === 'tertiary' &&
            'min-h-0 border-transparent px-2 py-1 text-xs',
        copied.value && 'border-[var(--success)] text-[var(--success)]',
    ),
);

const fallbackCopy = (value: string) => {
    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', 'true');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
};

const copy = async () => {
    if (props.disabled) {
        return;
    }

    error.value = '';

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(props.value);
        } else {
            fallbackCopy(props.value);
        }

        copied.value = true;
        window.clearTimeout(resetTimer);
        resetTimer = window.setTimeout(() => {
            copied.value = false;
        }, 1800);
    } catch {
        error.value = 'Clipboard blocked';
    }
};

onBeforeUnmount(() => {
    window.clearTimeout(resetTimer);
});
</script>

<template>
    <span class="inline-flex flex-col items-end gap-1">
        <button
            type="button"
            :class="buttonClass"
            :disabled="disabled"
            :title="error || label"
            @click="copy"
        >
            {{ copied ? copiedLabel : label }}
        </button>
        <span class="sr-only" aria-live="polite">
            {{ copied ? copiedLabel : error }}
        </span>
    </span>
</template>
