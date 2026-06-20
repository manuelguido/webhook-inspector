<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import CurlExample from '@/components/webhooks/CurlExample.vue';
import RequestDetail from '@/components/webhooks/RequestDetail.vue';
import RequestList from '@/components/webhooks/RequestList.vue';
import ErrorBanner from '@/components/webhooks/ui/ErrorBanner.vue';
import StatusBadge from '@/components/webhooks/ui/StatusBadge.vue';
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
    document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
        ?.content ?? '';

const selectedRequest = computed(
    () =>
        requests.value.find((request) => request.id === selectedId.value) ??
        null,
);

const lastRefreshedLabel = computed(() => {
    if (!lastRefreshedAt.value) {
        return 'Live polling ready';
    }

    return `Updated ${lastRefreshedAt.value.toLocaleTimeString()}`;
});

const requestCountLabel = computed(() =>
    requests.value.length === 1
        ? '1 request'
        : `${requests.value.length} requests`,
);

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

        const payload = (await response.json()) as {
            requests: CapturedWebhookRequest[];
        };
        replaceRequests(payload.requests);
        lastRefreshedAt.value = new Date();
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Unable to refresh requests.';
    } finally {
        isRefreshing.value = false;
    }
};

const clearRequests = async () => {
    if (!requests.value.length || isClearing.value) {
        return;
    }

    if (!window.confirm('Clear all captured requests for this endpoint?')) {
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
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Unable to clear requests.';
    } finally {
        isClearing.value = false;
    }
};

onMounted(() => {
    pollTimer = window.setInterval(
        refreshRequests,
        props.limits.poll_interval_ms,
    );
});

onBeforeUnmount(() => {
    window.clearInterval(pollTimer);
});
</script>

<template>
    <Head title="Webhook Inspector" />

    <main class="inspector-page px-4 py-4 sm:px-6 lg:px-8">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-4">
            <header
                class="flex flex-col gap-3 border-b border-[var(--inspector-border-soft)] pb-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="min-w-0">
                    <p class="inspector-section-label">Webhook Inspector</p>
                    <h1
                        class="mt-1 text-2xl font-bold tracking-normal text-[var(--inspector-fg)] sm:text-3xl"
                    >
                        Capture and inspect incoming webhooks
                    </h1>
                    <p
                        class="mt-1 max-w-2xl text-sm leading-6 font-light text-[var(--inspector-muted)]"
                    >
                        A focused request bin for debugging headers, query
                        strings, parsed JSON and raw payloads.
                    </p>
                </div>

                <div class="flex flex-col gap-3 lg:items-end">
                    <div class="flex flex-wrap items-center gap-2">
                        <StatusBadge tone="success">No login</StatusBadge>
                        <StatusBadge tone="muted"
                            >Polling
                            {{ limits.poll_interval_ms / 1000 }}s</StatusBadge
                        >
                        <StatusBadge tone="muted">{{
                            lastRefreshedLabel
                        }}</StatusBadge>
                    </div>
                    <div class="flex flex-wrap gap-2">
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
                </div>
            </header>

            <ErrorBanner
                v-if="error"
                :message="error"
                @retry="refreshRequests"
            />

            <WebhookUrlPanel
                :bin="bin"
                :limits="limits"
                :request-count="requests.length"
            />
            <CurlExample :webhook-url="bin.webhook_url" />

            <section
                aria-labelledby="request-workspace-title"
                class="grid gap-4 lg:grid-cols-[minmax(280px,0.42fr)_minmax(0,1fr)]"
            >
                <div
                    class="flex items-center justify-between gap-3 lg:col-span-2"
                >
                    <div>
                        <p
                            class="inspector-section-label"
                            id="request-workspace-title"
                        >
                            Request workspace
                        </p>
                        <p
                            class="mt-1 text-sm font-light text-[var(--inspector-muted)]"
                        >
                            {{ requestCountLabel }} captured for this endpoint.
                        </p>
                    </div>
                </div>

                <RequestList
                    :is-refreshing="isRefreshing"
                    :requests="requests"
                    :selected-id="selectedId"
                    :webhook-url="bin.webhook_url"
                    @select="selectedId = $event.id"
                />

                <RequestDetail :request="selectedRequest" />
            </section>

            <details
                class="rounded-[var(--inspector-radius)] border border-[var(--inspector-border-soft)] bg-[rgba(255,255,255,0.012)] p-3"
            >
                <summary
                    class="cursor-pointer text-sm font-bold text-[var(--inspector-muted)]"
                >
                    About this demo
                </summary>
                <div
                    class="mt-3 grid gap-3 text-sm leading-6 font-light text-[var(--inspector-muted)] sm:grid-cols-2 lg:grid-cols-4"
                >
                    <p>
                        <strong class="font-bold text-[var(--inspector-fg)]"
                            >Capture</strong
                        ><br />GET, POST, PUT, PATCH and DELETE.
                    </p>
                    <p>
                        <strong class="font-bold text-[var(--inspector-fg)]"
                            >Inspect</strong
                        ><br />Headers, query, JSON and raw body.
                    </p>
                    <p>
                        <strong class="font-bold text-[var(--inspector-fg)]"
                            >Retain</strong
                        ><br />Latest requests with body truncation.
                    </p>
                    <p>
                        <strong class="font-bold text-[var(--inspector-fg)]"
                            >Refresh</strong
                        ><br />Manual refresh plus simple polling.
                    </p>
                </div>
            </details>
        </div>
    </main>
</template>
