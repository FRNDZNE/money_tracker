<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const showFlash = ref(false);
const flashMessage = computed(() => flashSuccess.value || flashError.value);
const isSuccessFlash = computed(() => !!flashSuccess.value);

watch(flashMessage, (msg) => {
    if (msg) {
        showFlash.value = true;
        setTimeout(() => {
            showFlash.value = false;
        }, 4000);
    }
}, { immediate: true });

const userInitials = computed(() => {
    const name = page.props.auth?.user?.name || '';
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const sidebarOpen = ref(false);
</script>

<template>
    <div class="flex h-screen bg-slate-50 overflow-hidden">
        <!-- Mobile Overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black/60 z-20 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 flex flex-col transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 shrink-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800 shrink-0">
                <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-white font-bold text-sm leading-tight">Money Tracker</p>
                    <p class="text-slate-500 text-xs">Personal Finance</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                <!-- Dashboard -->
                <Link
                    :href="route('dashboard')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('dashboard')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </Link>

                <!-- Accounts -->
                <Link
                    :href="route('accounts.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('accounts*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Accounts
                </Link>

                <!-- Transactions -->
                <Link
                    :href="route('transactions.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('transactions*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                    Transactions
                </Link>

                <!-- Transfers -->
                <Link
                    :href="route('transfers.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('transfers*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transfers
                </Link>

                <!-- Recurring -->
                <Link
                    :href="route('recurring-transactions.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('recurring-transactions*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                    </svg>
                    Recurring
                </Link>

                <!-- Categories -->
                <Link
                    :href="route('categories.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('categories*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Categories
                </Link>

                <!-- Divider -->
                <div class="my-2 border-t border-slate-800" />
                <p class="px-3 text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Planning</p>

                <!-- Budgets -->
                <Link
                    :href="route('budgets.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('budgets*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                    Budgets
                </Link>

                <!-- Savings Goals -->
                <Link
                    :href="route('savings-goals.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('savings-goals*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                    Savings Goals
                </Link>

                <!-- Divider -->
                <div class="my-2 border-t border-slate-800" />
                <p class="px-3 text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Insights</p>

                <!-- Reports -->
                <Link
                    :href="route('reports.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('reports*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Reports
                </Link>

                <!-- Analytics -->
                <Link
                    :href="route('analytics.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('analytics*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    Analytics
                </Link>

                <!-- Insights -->
                <Link
                    :href="route('insights.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                        route().current('insights*')
                            ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/40'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                    </svg>
                    Insights
                </Link>
            </nav>

            <!-- User + Logout -->
            <div class="px-3 py-4 border-t border-slate-800 shrink-0 space-y-1">
                <Link
                    :href="route('profile.edit')"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 transition-colors duration-150 group"
                >
                    <div class="w-8 h-8 bg-emerald-500/20 rounded-full flex items-center justify-center shrink-0">
                        <span class="text-emerald-400 text-xs font-bold">{{ userInitials }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-200 truncate group-hover:text-white">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="text-xs text-slate-500 truncate">{{ $page.props.auth.user.email }}</p>
                    </div>
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition-all duration-150"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign Out
                </Link>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Flash Notification -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="showFlash && flashMessage"
                    :class="[
                        'absolute top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium',
                        isSuccessFlash
                            ? 'bg-emerald-600 text-white'
                            : 'bg-rose-600 text-white',
                    ]"
                >
                    <svg v-if="isSuccessFlash" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ flashMessage }}
                    <button @click="showFlash = false" class="ml-2 opacity-80 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </Transition>

            <!-- Mobile Top Bar -->
            <div class="lg:hidden flex items-center gap-3 px-4 py-3 bg-white border-b border-slate-200 shrink-0">
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="font-semibold text-slate-800">Money Tracker</span>
            </div>

            <!-- Page Heading -->
            <header
                v-if="$slots.header"
                class="bg-white border-b border-slate-200 px-6 lg:px-8 py-4 lg:py-5 shrink-0"
            >
                <slot name="header" />
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
