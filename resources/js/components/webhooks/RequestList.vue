<script setup lang="ts">
import type { CapturedWebhookRequest } from '@/types/webhooks';

defineProps<{
    requests: CapturedWebhookRequest[];
    selectedId: number | null;
}>();

const emit = defineEmits<{
    select: [request: CapturedWebhookRequest];
}>();

const methodClass = (method: string) => {
    const normalized = method.toUpperCase();

    if (normalized === 'GET') {
        return 'border-[rgba(125,211,252,0.35)] text-[var(--inspector-accent)]';
    }

    if (normalized === 'POST') {
        return 'border-[rgba(103,232,165,0.35)] text-[var(--inspector-green)]';
    }

    if (['PUT', 'PATCH'].includes(normalized)) {
        return 'border-[rgba(244,198,118,0.35)] text-[var(--inspector-amber)]';
    }

    return 'border-[rgba(251,143,143,0.35)] text-[var(--inspector-red)]';
};

const formatTime = (value: string) =>
    new Intl.DateTimeFormat(undefined, {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(new Date(value));
</script>

<template>
    <section class="inspector-panel flex min-h-[420px] flex-col overflow-hidden">
        <div class="inspector-panel-header flex items-center justify-between gap-3 p-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--inspector-accent)]">
                    Incoming traffic
                </p>
                <h2 class="mt-1 text-base font-semibold text-[var(--inspector-fg)]">Captured requests</h2>
            </div>
            <span class="inspector-badge">{{ requests.length }} total</span>
        </div>

        <div v-if="requests.length" class="max-h-[640px] overflow-auto">
            <button
                v-for="request in requests"
                :key="request.id"
                type="button"
                class="inspector-focus block w-full border-b border-[var(--inspector-border)] p-4 text-left transition hover:bg-[rgba(255,255,255,0.025)]"
                :class="{ 'bg-[var(--inspector-accent-soft)]': selectedId === request.id }"
                @click="emit('select', request)"
            >
                <div class="flex items-center justify-between gap-3">
                    <span class="inspector-badge" :class="methodClass(request.method)">
                        {{ request.method }}
                    </span>
                    <span class="inspector-badge border-[rgba(103,232,165,0.25)] text-[var(--inspector-green)]">
                        captured
                    </span>
                </div>
                <p class="mt-3 truncate text-sm font-medium text-[var(--inspector-fg)]">
                    {{ request.path }}
                </p>
                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-[var(--inspector-faint)]">
                    <span>{{ formatTime(request.captured_at) }}</span>
                    <span>{{ request.content_type || 'no content type' }}</span>
                    <span>{{ request.body_size }} bytes</span>
                </div>
            </button>
        </div>

        <div v-else class="flex flex-1 items-center justify-center p-4 text-center">
            <div>
                <p class="text-sm font-semibold text-[var(--inspector-fg)]">No requests yet</p>
                <p class="mt-2 text-sm leading-6 text-[var(--inspector-muted)]">
                    Send the curl example or point any webhook sender at the URL above.
                </p>
            </div>
        </div>
    </section>
</template>
