<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import CurlExample from '@/components/webhooks/CurlExample.vue';
import RequestDetail from '@/components/webhooks/RequestDetail.vue';
import RequestList from '@/components/webhooks/RequestList.vue';
import WebhookUrlPanel from '@/components/webhooks/WebhookUrlPanel.vue';
import type {
    CapturedWebhookRequest,
    InspectorLimits,
    InspectorUrls,
    WebhookBin,
} from '@/types/webhooks';

const props = defineProps<{
    bin: WebhookBin;
    requests: CapturedWebhookRequest[];
    limits: InspectorLimits;
    urls: InspectorUrls;
}>();

const bin = ref(props.bin);
const requests = ref<CapturedWebhookRequest[]>(props.requests);
const selectedId = ref<number | null>(props.requests[0]?.id ?? null);
const isRefreshing = ref(false);
const isClearing = ref(false);
const error = ref('');
const lastRefreshedAt = ref<Date | null>(null);
let pollTimer: number | undefined;

const csrfToken = () =>
    document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

const selectedRequest = computed(
    () => requests.value.find((request) => request.id === selectedId.value) ?? null,
);

const lastRefreshedLabel = computed(() => {
    if (!lastRefreshedAt.value) {
        return 'Live polling ready';
    }

    return `Updated ${lastRefreshedAt.value.toLocaleTimeString()}`;
});

const replaceRequests = (nextRequests: CapturedWebhookRequest[]) => {
    requests.value = nextRequests;

    if (!requests.value.some((request) => request.id === selectedId.value)) {
        selectedId.value = requests.value[0]?.id ?? null;
    }
};

const refreshRequests = async () => {
    isRefreshing.value = true;
    error.value = '';

    try {
        const response = await fetch(bin.value.requests_url, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Refresh failed with HTTP ${response.status}.`);
        }

        const payload = (await response.json()) as { requests: CapturedWebhookRequest[] };
        replaceRequests(payload.requests);
        lastRefreshedAt.value = new Date();
    } catch (exception) {
        error.value = exception instanceof Error ? exception.message : 'Unable to refresh requests.';
    } finally {
        isRefreshing.value = false;
    }
};

const clearRequests = async () => {
    if (!requests.value.length || isClearing.value) {
        return;
    }

    isClearing.value = true;
    error.value = '';

    try {
        const response = await fetch(bin.value.clear_url, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        });

        if (!response.ok) {
            throw new Error(`Clear failed with HTTP ${response.status}.`);
        }

        replaceRequests([]);
        lastRefreshedAt.value = new Date();
    } catch (exception) {
        error.value = exception instanceof Error ? exception.message : 'Unable to clear requests.';
    } finally {
        isClearing.value = false;
    }
};

onMounted(() => {
    pollTimer = window.setInterval(refreshRequests, props.limits.poll_interval_ms);
});

onBeforeUnmount(() => {
    window.clearInterval(pollTimer);
});
</script>

<template>
    <Head title="Webhook Inspector" />

    <main class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(125,211,252,0.11),transparent_30%),linear-gradient(180deg,var(--inspector-bg-2),var(--inspector-bg))] px-4 py-5 sm:px-6 lg:px-8">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-5">
            <header class="flex flex-col gap-4 py-2 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--inspector-accent)]">
                        Webhook Inspector
                    </p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-normal text-[var(--inspector-fg)] sm:text-4xl">
                        Capture, inspect, and debug incoming webhooks.
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[var(--inspector-muted)]">
                        A public request bin for developer workflows: send any HTTP request, inspect headers,
                        query params, parsed JSON, raw payloads and truncation state.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inspector-badge border-[rgba(103,232,165,0.35)] text-[var(--inspector-green)]">
                        no login
                    </span>
                    <span class="inspector-badge">polling {{ limits.poll_interval_ms / 1000 }}s</span>
                    <span class="inspector-badge">{{ lastRefreshedLabel }}</span>
                </div>
            </header>

            <div v-if="error" class="border border-[rgba(251,143,143,0.35)] bg-[rgba(251,143,143,0.08)] p-3 text-sm text-[var(--inspector-red)]">
                {{ error }}
            </div>

            <div class="grid gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(380px,0.8fr)]">
                <div class="flex flex-col gap-5">
                    <WebhookUrlPanel :bin="bin" :limits="limits" :request-count="requests.length" />
                    <CurlExample :webhook-url="bin.webhook_url" />

                    <section class="inspector-panel overflow-hidden">
                        <div class="inspector-panel-header p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--inspector-accent)]">
                                What this demo shows
                            </p>
                        </div>
                        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="border border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-3">
                                <p class="text-sm font-semibold text-[var(--inspector-fg)]">Request capture</p>
                                <p class="mt-1 text-xs leading-5 text-[var(--inspector-muted)]">GET, POST, PUT, PATCH and DELETE.</p>
                            </div>
                            <div class="border border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-3">
                                <p class="text-sm font-semibold text-[var(--inspector-fg)]">Payload inspection</p>
                                <p class="mt-1 text-xs leading-5 text-[var(--inspector-muted)]">Headers, query, JSON and raw body.</p>
                            </div>
                            <div class="border border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-3">
                                <p class="text-sm font-semibold text-[var(--inspector-fg)]">Safe demo storage</p>
                                <p class="mt-1 text-xs leading-5 text-[var(--inspector-muted)]">SQLite retention with body truncation.</p>
                            </div>
                            <div class="border border-[var(--inspector-border)] bg-[rgba(255,255,255,0.015)] p-3">
                                <p class="text-sm font-semibold text-[var(--inspector-fg)]">Live debugging</p>
                                <p class="mt-1 text-xs leading-5 text-[var(--inspector-muted)]">Manual refresh plus simple polling.</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="flex flex-col gap-5">
                    <div class="flex flex-wrap justify-end gap-2">
                        <button
                            type="button"
                            class="inspector-btn inspector-btn-primary inspector-focus"
                            :disabled="isRefreshing"
                            @click="refreshRequests"
                        >
                            {{ isRefreshing ? 'Refreshing...' : 'Refresh' }}
                        </button>
                        <button
                            type="button"
                            class="inspector-btn inspector-btn-danger inspector-focus"
                            :disabled="isClearing || requests.length === 0"
                            @click="clearRequests"
                        >
                            {{ isClearing ? 'Clearing...' : 'Clear requests' }}
                        </button>
                    </div>

                    <RequestList
                        :requests="requests"
                        :selected-id="selectedId"
                        @select="selectedId = $event.id"
                    />
                </div>
            </div>

            <RequestDetail :request="selectedRequest" />
        </div>
    </main>
</template>
