<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classification_breakdown: Object,
    category_breakdown: Array,
    total_expense: Number,
    daily_expenses: Array,
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
    router.get(route('analytics.index'), {
        month: selectedMonth.value,
        year: selectedYear.value,
    }, { preserveState: true, replace: true });
}

const formatIDR = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);

const formatCompact = (n) => {
    if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
    if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
    return n.toString();
};

// Classification donut (SVG)
const classifications = computed(() => {
    const b = props.classification_breakdown;
    const total = b.need + b.want + b.investment + b.unclassified || 1;
    return [
        { key: 'need', label: 'Need', amount: b.need, color: '#10b981', pct: (b.need / total) * 100 },
        { key: 'want', label: 'Want', amount: b.want, color: '#f59e0b', pct: (b.want / total) * 100 },
        { key: 'investment', label: 'Investment', amount: b.investment, color: '#6366f1', pct: (b.investment / total) * 100 },
        { key: 'unclassified', label: 'Unclassified', amount: b.unclassified, color: '#94a3b8', pct: (b.unclassified / total) * 100 },
    ].filter(c => c.amount > 0);
});

// SVG donut helper
const RADIUS = 80;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

function segmentDash(pct) {
    const len = (pct / 100) * CIRCUMFERENCE;
    return `${len} ${CIRCUMFERENCE - len}`;
}

const segmentOffsets = computed(() => {
    let offset = 0;
    return classifications.value.map(c => {
        const o = offset;
        offset += (c.pct / 100) * CIRCUMFERENCE;
        return o;
    });
});

// Daily expense chart data
const maxDailyExpense = computed(() => {
    if (!props.daily_expenses?.length) return 0;
    return Math.max(...props.daily_expenses.map(d => d.total));
});

const dailyAverage = computed(() => {
    if (!props.daily_expenses?.length) return 0;
    const daysWithExpense = props.daily_expenses.filter(d => d.total > 0);
    if (daysWithExpense.length === 0) return 0;
    return daysWithExpense.reduce((sum, d) => sum + d.total, 0) / daysWithExpense.length;
});

const highestDay = computed(() => {
    if (!props.daily_expenses?.length) return null;
    return props.daily_expenses.reduce((max, d) => d.total > max.total ? d : max, props.daily_expenses[0]);
});

const hoveredDay = ref(null);
</script>

<template>
    <Head title="Expense Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Expense Analytics</h1>
                <p class="text-sm text-slate-500 mt-0.5">Spending classification, category breakdown & daily trends</p>
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
                <span class="ml-auto text-sm text-slate-500">
                    Total: <span class="font-semibold text-slate-800">{{ formatIDR(total_expense) }}</span>
                </span>
            </div>

            <div v-if="total_expense === 0" class="text-center py-20">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800 mb-1">No expense data</h3>
                <p class="text-sm text-slate-500">No expenses found for {{ months[filters.month - 1] }} {{ filters.year }}.</p>
            </div>

            <div v-else class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Classification Donut -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <h2 class="text-sm font-semibold text-slate-800 mb-6">Spending Classification</h2>

                        <div class="flex flex-col sm:flex-row items-center gap-6">
                            <!-- SVG Donut -->
                            <div class="relative w-48 h-48 shrink-0">
                                <svg viewBox="0 0 200 200" class="w-48 h-48 -rotate-90">
                                    <circle cx="100" cy="100" r="80" fill="none" stroke="#f1f5f9" stroke-width="28" />
                                    <circle
                                        v-for="(seg, i) in classifications"
                                        :key="seg.key"
                                        cx="100" cy="100" r="80"
                                        fill="none"
                                        :stroke="seg.color"
                                        stroke-width="28"
                                        :stroke-dasharray="segmentDash(seg.pct)"
                                        :stroke-dashoffset="-segmentOffsets[i]"
                                        class="transition-all duration-700"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-xs text-slate-500">Total</span>
                                    <span class="text-sm font-bold text-slate-800">{{ formatIDR(total_expense) }}</span>
                                </div>
                            </div>

                            <!-- Legend -->
                            <div class="flex-1 space-y-3">
                                <div v-for="seg in classifications" :key="seg.key" class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full shrink-0" :style="{ background: seg.color }" />
                                        <span class="text-sm text-slate-700 capitalize">{{ seg.label }}</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-slate-800">{{ formatIDR(seg.amount) }}</p>
                                        <p class="text-xs text-slate-400">{{ seg.pct.toFixed(1) }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100">
                            <h2 class="text-sm font-semibold text-slate-800">Expenses by Category</h2>
                        </div>
                        <ul class="divide-y divide-slate-100">
                            <li v-for="cat in category_breakdown" :key="cat.category_id" class="px-6 py-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-slate-800">{{ cat.category_name }}</span>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-slate-800">{{ formatIDR(cat.total) }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ total_expense > 0 ? Math.round((cat.total / total_expense) * 100) : 0 }}% · {{ cat.count }} txn
                                        </p>
                                    </div>
                                </div>
                                <!-- Main progress bar -->
                                <div class="w-full bg-slate-100 rounded-full h-2 mb-2">
                                    <div
                                        class="h-2 rounded-full bg-indigo-400"
                                        :style="{ width: total_expense > 0 ? Math.min(100, Math.round((cat.total / total_expense) * 100)) + '%' : '0%' }"
                                    />
                                </div>
                                <!-- Classification mini bars -->
                                <div class="flex items-center gap-1 h-1">
                                    <div class="bg-emerald-400 rounded-full h-1" :style="{ width: cat.total > 0 ? (cat.need / cat.total * 100) + '%' : '0%' }" :title="'Need: ' + formatIDR(cat.need)" />
                                    <div class="bg-amber-400 rounded-full h-1" :style="{ width: cat.total > 0 ? (cat.want / cat.total * 100) + '%' : '0%' }" :title="'Want: ' + formatIDR(cat.want)" />
                                    <div class="bg-indigo-400 rounded-full h-1" :style="{ width: cat.total > 0 ? (cat.investment / cat.total * 100) + '%' : '0%' }" :title="'Investment: ' + formatIDR(cat.investment)" />
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Daily Expenses Chart -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800">Daily Expenses</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Day-by-day spending for {{ months[filters.month - 1] }} {{ filters.year }}</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-slate-500">
                            <span>Avg: <strong class="text-slate-700">{{ formatIDR(dailyAverage) }}</strong></span>
                            <span v-if="highestDay && highestDay.total > 0">Peak: <strong class="text-rose-600">Day {{ highestDay.day }}</strong></span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-1">
                            <div
                                v-for="day in daily_expenses"
                                :key="day.day"
                                class="flex items-center gap-3 group cursor-default"
                                @mouseenter="hoveredDay = day.day"
                                @mouseleave="hoveredDay = null"
                            >
                                <!-- Day label -->
                                <div class="w-14 shrink-0 flex items-center gap-1.5">
                                    <span :class="['text-xs font-medium tabular-nums w-5 text-right', day.is_weekend ? 'text-rose-400' : 'text-slate-500']">{{ day.day }}</span>
                                    <span :class="['text-xs w-7', day.is_weekend ? 'text-rose-400' : 'text-slate-400']">{{ day.day_label }}</span>
                                </div>

                                <!-- Bar -->
                                <div class="flex-1 h-6 relative">
                                    <div :class="['absolute inset-0 rounded', day.is_weekend ? 'bg-rose-50' : 'bg-slate-50']" />
                                    <div
                                        v-if="day.total > 0"
                                        :class="['absolute inset-y-0 left-0 rounded transition-all duration-300', hoveredDay === day.day ? 'bg-gradient-to-r from-rose-500 to-rose-400' : 'bg-gradient-to-r from-indigo-500 to-indigo-400']"
                                        :style="{ width: maxDailyExpense > 0 ? Math.max(2, (day.total / maxDailyExpense) * 100) + '%' : '0%' }"
                                    />
                                    <!-- Tooltip on hover -->
                                    <div
                                        v-if="hoveredDay === day.day && day.total > 0"
                                        class="absolute left-0 -top-8 bg-slate-800 text-white text-xs font-medium px-2.5 py-1 rounded shadow-lg z-10 whitespace-nowrap"
                                    >
                                        {{ formatIDR(day.total) }} · {{ day.count }} txn
                                    </div>
                                </div>

                                <!-- Amount -->
                                <div class="w-20 text-right shrink-0">
                                    <span v-if="day.total > 0" :class="['text-xs font-semibold tabular-nums', hoveredDay === day.day ? 'text-rose-600' : 'text-slate-700']">
                                        {{ formatCompact(day.total) }}
                                    </span>
                                    <span v-else class="text-xs text-slate-300">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Average line indicator -->
                        <div v-if="dailyAverage > 0 && maxDailyExpense > 0" class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-500">
                            <div class="w-3 h-0.5 bg-amber-400 rounded" />
                            <span>Daily Average: <strong class="text-slate-700">{{ formatIDR(dailyAverage) }}</strong> /day</span>
                            <span class="text-slate-300 mx-1">·</span>
                            <span>{{ daily_expenses.filter(d => d.total > 0).length }} active days out of {{ daily_expenses.length }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
