<script setup lang="ts">
import { computed, ref } from 'vue';

import CopyButton from '@/components/webhooks/CopyButton.vue';

const props = withDefaults(
    defineProps<{
        allowWrap?: boolean;
        copyLabel?: string;
        copyable?: boolean;
        initialWrap?: boolean;
        label?: string;
        language?: 'json' | 'text';
        tone?: 'accent' | 'default';
        value: string;
    }>(),
    {
        allowWrap: false,
        copyLabel: 'Copy',
        copyable: false,
        initialWrap: false,
        label: '',
        language: 'text',
        tone: 'default',
    },
);

const isWrapped = ref(props.initialWrap);

const escapeHtml = (value: string) =>
    value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

const highlightedValue = computed(() => {
    const escaped = escapeHtml(props.value);

    if (props.language !== 'json') {
        return escaped;
    }

    return escaped.replace(
        /(&quot;(?:\\.|[^\\])*?&quot;)(\s*:)?|\b(true|false|null)\b|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/g,
        (match, stringValue: string, keySuffix: string, literal: string) => {
            if (stringValue && keySuffix) {
                return `<span class="json-key">${stringValue}</span>${keySuffix}`;
            }

            if (stringValue) {
                return `<span class="json-string">${stringValue}</span>`;
            }

            if (literal) {
                return `<span class="json-literal">${literal}</span>`;
            }

            return `<span class="json-number">${match}</span>`;
        },
    );
});
</script>

<template>
    <div
        class="overflow-hidden rounded-[var(--radius-md)] border border-[var(--border-subtle)] bg-[var(--code-bg)]"
    >
        <div
            v-if="label || copyable || allowWrap"
            class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--border-subtle)] px-3 py-2"
        >
            <p v-if="label" class="text-xs font-light text-[var(--text-muted)]">
                {{ label }}
            </p>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    v-if="allowWrap"
                    type="button"
                    class="inspector-btn inspector-focus min-h-0 border-transparent px-2 py-1 text-xs"
                    :aria-pressed="isWrapped"
                    @click="isWrapped = !isWrapped"
                >
                    {{ isWrapped ? 'No wrap' : 'Wrap' }}
                </button>
                <CopyButton
                    v-if="copyable"
                    :value="value"
                    :label="copyLabel"
                    copied-label="Copied"
                    variant="tertiary"
                />
            </div>
        </div>

        <pre
            class="code-content max-h-[min(62dvh,620px)] overflow-auto p-3 text-[0.8125rem] leading-6"
            :class="[
                isWrapped
                    ? 'break-words whitespace-pre-wrap'
                    : 'whitespace-pre',
                tone === 'accent'
                    ? 'text-[var(--accent)]'
                    : 'text-[var(--text-secondary)]',
            ]"
        ><code v-html="highlightedValue"></code></pre>
    </div>
</template>
