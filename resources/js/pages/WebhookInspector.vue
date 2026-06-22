<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

import ContextSidebar from '@/components/webhooks/ContextSidebar.vue';
import CurlExample from '@/components/webhooks/CurlExample.vue';
import InspectorHeader from '@/components/webhooks/InspectorHeader.vue';
import NavigationRail from '@/components/webhooks/NavigationRail.vue';
import RequestDetail from '@/components/webhooks/RequestDetail.vue';
import ErrorBanner from '@/components/webhooks/ui/ErrorBanner.vue';
import WorkbenchDrawer from '@/components/webhooks/WorkbenchDrawer.vue';
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
const sidebarOpen = ref(false);
const quickTestOpen = ref(false);
const aboutOpen = ref(false);
const theme = ref<'dark' | 'light'>('dark');
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

const selectRequest = (request: CapturedWebhookRequest) => {
    selectedId.value = request.id;

    if (window.matchMedia('(width <= 1100px)').matches) {
        sidebarOpen.value = false;
    }
};

const focusEndpoint = () => {
    sidebarOpen.value = true;
    requestAnimationFrame(() => {
        document.getElementById('endpoint-summary')?.focus();
    });
};

const focusInspector = () => {
    requestAnimationFrame(() => {
        document.getElementById('inspector-workspace')?.focus();
    });
};

const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
};

watch(theme, (nextTheme) => {
    document.documentElement.dataset.theme = nextTheme;
    window.localStorage.setItem('webhook-inspector-theme', nextTheme);
});

onMounted(() => {
    const storedTheme = window.localStorage.getItem('webhook-inspector-theme');

    if (storedTheme === 'dark' || storedTheme === 'light') {
        theme.value = storedTheme;
    } else {
        document.documentElement.dataset.theme = theme.value;
    }

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

    <main class="app-shell">
        <NavigationRail
            :request-count="requests.length"
            :sidebar-open="sidebarOpen"
            :theme="theme"
            @focus-endpoint="focusEndpoint"
            @focus-inspector="focusInspector"
            @open-about="aboutOpen = true"
            @open-quick-test="quickTestOpen = true"
            @toggle-sidebar="sidebarOpen = !sidebarOpen"
            @toggle-theme="toggleTheme"
        />

        <button
            v-if="sidebarOpen"
            type="button"
            class="workbench-drawer-backdrop fixed inset-0 z-20 hidden cursor-default max-[1100px]:block"
            aria-label="Close request sidebar"
            @click="sidebarOpen = false"
        />

        <aside
            id="context-sidebar"
            class="context-sidebar z-30"
            :data-open="sidebarOpen"
        >
            <ContextSidebar
                :bin="bin"
                :is-refreshing="isRefreshing"
                :last-refreshed-label="lastRefreshedLabel"
                :limits="limits"
                :request-count="requests.length"
                :requests="requests"
                :selected-id="selectedId"
                @close="sidebarOpen = false"
                @open-quick-test="quickTestOpen = true"
                @select="selectRequest"
            />
        </aside>

        <section class="inspector-workspace flex min-h-dvh min-w-0 flex-col">
            <InspectorHeader
                :is-clearing="isClearing"
                :is-refreshing="isRefreshing"
                :last-refreshed-label="lastRefreshedLabel"
                :limits="limits"
                :request="selectedRequest"
                :request-count="requests.length"
                @clear-requests="clearRequests"
                @refresh-requests="refreshRequests"
                @toggle-sidebar="sidebarOpen = !sidebarOpen"
            />

            <ErrorBanner
                v-if="error"
                class="mx-5 mt-4"
                :message="error"
                @retry="refreshRequests"
            />

            <RequestDetail
                :request="selectedRequest"
                :webhook-url="bin.webhook_url"
                @open-quick-test="quickTestOpen = true"
            />
        </section>

        <WorkbenchDrawer
            :open="quickTestOpen"
            title="Quick test"
            description="A ready-made request for validating capture, headers, query parameters and JSON parsing."
            @close="quickTestOpen = false"
        >
            <CurlExample :webhook-url="bin.webhook_url" />
        </WorkbenchDrawer>

        <WorkbenchDrawer
            :open="aboutOpen"
            title="About this demo"
            description="What this workbench demonstrates and what it intentionally keeps simple."
            @close="aboutOpen = false"
        >
            <div
                class="grid gap-4 text-sm leading-6 text-[var(--text-secondary)]"
            >
                <p>
                    This public endpoint accepts GET, POST, PUT, PATCH and
                    DELETE requests for inspection during development.
                </p>
                <div
                    class="grid gap-3 sm:grid-cols-2"
                    aria-label="Demo capabilities"
                >
                    <div class="inspector-subtle-card p-3">
                        <h3 class="font-bold text-[var(--text-primary)]">
                            Capture
                        </h3>
                        <p class="mt-1 font-light">
                            Incoming method, URL, headers, query parameters,
                            JSON and raw request bodies.
                        </p>
                    </div>
                    <div class="inspector-subtle-card p-3">
                        <h3 class="font-bold text-[var(--text-primary)]">
                            Inspect
                        </h3>
                        <p class="mt-1 font-light">
                            Request metadata, parsed JSON, headers, query values
                            and raw payloads stay available in tabs.
                        </p>
                    </div>
                    <div class="inspector-subtle-card p-3">
                        <h3 class="font-bold text-[var(--text-primary)]">
                            Retain
                        </h3>
                        <p class="mt-1 font-light">
                            The session keeps the latest
                            <span class="technical-value">{{
                                limits.max_requests_per_bin
                            }}</span>
                            requests and enforces the configured body limit.
                        </p>
                    </div>
                    <div class="inspector-subtle-card p-3">
                        <h3 class="font-bold text-[var(--text-primary)]">
                            Refresh
                        </h3>
                        <p class="mt-1 font-light">
                            Manual refresh works alongside polling every
                            <span class="technical-value"
                                >{{ limits.poll_interval_ms / 1000 }}s</span
                            >.
                        </p>
                    </div>
                </div>
            </div>
        </WorkbenchDrawer>
    </main>
</template>
