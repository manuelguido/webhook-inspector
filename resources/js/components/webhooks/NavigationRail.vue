<script setup lang="ts">
defineProps<{
    requestCount: number;
    sidebarOpen: boolean;
    theme: 'dark' | 'light';
}>();

const emit = defineEmits<{
    focusEndpoint: [];
    focusInspector: [];
    openAbout: [];
    openQuickTest: [];
    toggleSidebar: [];
    toggleTheme: [];
}>();
</script>

<template>
    <nav
        class="navigation-rail flex h-dvh flex-col items-center justify-between px-2 py-3 max-[700px]:h-14 max-[700px]:flex-row max-[700px]:px-3 max-[700px]:py-2"
        aria-label="Webhook Inspector"
    >
        <div
            class="flex flex-col items-center gap-2 max-[700px]:flex-row"
            role="list"
        >
            <button
                type="button"
                class="inspector-focus flex h-10 w-10 items-center justify-center rounded-[var(--radius-sm)] bg-[var(--accent)] text-sm font-bold text-[#f8fbff] shadow-sm transition hover:bg-[var(--accent-strong)]"
                aria-current="page"
                aria-label="Inspector workspace"
                title="Inspector workspace"
                @click="emit('focusInspector')"
            >
                WI
            </button>

            <button
                type="button"
                class="inspector-focus relative flex h-10 w-10 items-center justify-center rounded-[var(--radius-sm)] text-xs font-bold text-[var(--text-secondary)] transition hover:bg-[var(--surface-hover)] hover:text-[var(--text-primary)]"
                :aria-expanded="sidebarOpen"
                aria-controls="context-sidebar"
                aria-label="Toggle request inbox"
                title="Request inbox"
                @click="emit('toggleSidebar')"
            >
                RQ
                <span
                    v-if="requestCount"
                    class="absolute -top-1 -right-1 min-w-5 rounded-full bg-[var(--accent)] px-1 text-center text-[0.62rem] leading-5 text-[#f8fbff]"
                    aria-hidden="true"
                >
                    {{ requestCount > 99 ? '99+' : requestCount }}
                </span>
            </button>

            <button
                type="button"
                class="inspector-focus flex h-10 w-10 items-center justify-center rounded-[var(--radius-sm)] text-xs font-bold text-[var(--text-secondary)] transition hover:bg-[var(--surface-hover)] hover:text-[var(--text-primary)]"
                aria-label="Endpoint"
                title="Endpoint"
                @click="emit('focusEndpoint')"
            >
                EP
            </button>

            <button
                type="button"
                class="inspector-focus flex h-10 w-10 items-center justify-center rounded-[var(--radius-sm)] text-xs font-bold text-[var(--text-secondary)] transition hover:bg-[var(--surface-hover)] hover:text-[var(--text-primary)]"
                aria-label="Open quick test"
                title="Quick test"
                @click="emit('openQuickTest')"
            >
                cURL
            </button>
        </div>

        <div
            class="flex flex-col items-center gap-2 max-[700px]:flex-row"
            role="list"
        >
            <button
                type="button"
                class="inspector-focus flex h-10 w-10 items-center justify-center rounded-[var(--radius-sm)] text-sm font-bold text-[var(--text-secondary)] transition hover:bg-[var(--surface-hover)] hover:text-[var(--text-primary)]"
                aria-label="About this demo"
                title="About this demo"
                @click="emit('openAbout')"
            >
                ?
            </button>

            <button
                type="button"
                class="inspector-focus flex h-10 w-10 items-center justify-center rounded-[var(--radius-sm)] text-xs font-bold text-[var(--text-secondary)] transition hover:bg-[var(--surface-hover)] hover:text-[var(--text-primary)]"
                :aria-label="
                    theme === 'dark'
                        ? 'Switch to light theme'
                        : 'Switch to dark theme'
                "
                :title="
                    theme === 'dark'
                        ? 'Switch to light theme'
                        : 'Switch to dark theme'
                "
                @click="emit('toggleTheme')"
            >
                {{ theme === 'dark' ? 'LT' : 'DK' }}
            </button>
        </div>
    </nav>
</template>
