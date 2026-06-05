<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    insights: Array,
    filters: Object,
});

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const currentYear   = new Date().getFullYear();
const years         = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
const selectedMonth = ref(props.filters.month);
const selectedYear  = ref(props.filters.year);

function applyFilter() {
    router.get(route('insights.index'), {
        month: selectedMonth.value,
        year: selectedYear.value,
    }, { preserveState: true, replace: true });
}

const iconMap = {
    'piggy-bank': `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />`,
    'pie-chart': `<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />`,
    'trending-up': `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />`,
    'trending-down': `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />`,
    'alert-circle': `<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />`,
    'alert-triangle': `<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />`,
};

const typeStyles = {
    positive: { bg: 'bg-emerald-50', border: 'border-emerald-200', iconBg: 'bg-emerald-100', iconColor: 'text-emerald-600', valueBg: 'bg-emerald-100 text-emerald-700' },
    warning: { bg: 'bg-amber-50', border: 'border-amber-200', iconBg: 'bg-amber-100', iconColor: 'text-amber-600', valueBg: 'bg-amber-100 text-amber-700' },
    negative: { bg: 'bg-rose-50', border: 'border-rose-200', iconBg: 'bg-rose-100', iconColor: 'text-rose-600', valueBg: 'bg-rose-100 text-rose-700' },
    info: { bg: 'bg-blue-50', border: 'border-blue-200', iconBg: 'bg-blue-100', iconColor: 'text-blue-600', valueBg: 'bg-blue-100 text-blue-700' },
};

const getStyle = (type) => typeStyles[type] || typeStyles.info;
</script>

<template>
    <Head title="Financial Insights" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Financial Insights</h1>
                <p class="text-sm text-slate-500 mt-0.5">Smart tips and analysis based on your spending</p>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-6">
            <!-- Filter -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600">Month</label>
                    <select v-model="selectedMonth" class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option v-for="(name, i) in months" :key="i + 1" :value="i + 1">{{ name }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600">Year</label>
                    <select v-model="selectedYear" class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <button
                    @click="applyFilter"
                    class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    Apply
                </button>
            </div>

            <!-- Insights -->
            <div v-if="insights.length === 0" class="text-center py-20">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800 mb-1">No insights available</h3>
                <p class="text-sm text-slate-500">Add transactions for {{ months[filters.month - 1] }} {{ filters.year }} to get financial insights.</p>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div
                    v-for="(insight, i) in insights"
                    :key="i"
                    :class="['rounded-xl border p-5 transition-all duration-200 hover:shadow-md', getStyle(insight.type).bg, getStyle(insight.type).border]"
                >
                    <div class="flex items-start gap-4">
                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', getStyle(insight.type).iconBg]">
                            <svg :class="['w-5 h-5', getStyle(insight.type).iconColor]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" v-html="iconMap[insight.icon] || iconMap['alert-circle']" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h3 class="text-sm font-semibold text-slate-800">{{ insight.title }}</h3>
                                <span :class="['text-xs font-bold px-2.5 py-1 rounded-full whitespace-nowrap', getStyle(insight.type).valueBg]">
                                    {{ insight.value }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ insight.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
