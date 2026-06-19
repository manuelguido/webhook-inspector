<script setup lang="ts">
import { ref } from 'vue';

const props = withDefaults(
    defineProps<{
        value: string;
        label?: string;
        copiedLabel?: string;
        disabled?: boolean;
    }>(),
    {
        label: 'Copy',
        copiedLabel: 'Copied',
        disabled: false,
    },
);

const copied = ref(false);
const error = ref('');
let resetTimer: number | undefined;

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
</script>

<template>
    <button
        type="button"
        class="inspector-btn inspector-focus"
        :class="{ 'border-[rgba(103,232,165,0.35)] text-[var(--inspector-green)]': copied }"
        :disabled="disabled"
        :title="error || label"
        @click="copy"
    >
        {{ copied ? copiedLabel : label }}
    </button>
</template>
