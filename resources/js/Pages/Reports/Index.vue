<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    summaries: Array,
    available_years: Array,
    filters: Object,
});

const headers = [
    { text: "Month", value: "month" },
    { text: "Income", value: "income" },
    { text: "Expenses", value: "expense" },
    { text: "Net", value: "net" },
    { text: "Detail", value: "detail" },
];

const selectedYear = ref(props.filters.year);

function applyFilter() {
    router.get(route('reports.index'), { year: selectedYear.value }, { preserveState: true, replace: true });
}

const formatIDR = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);

// Chart calculations
const maxValue = computed(() => {
    const vals = props.summaries.flatMap(s => [s.income, s.expense]);
    return Math.max(...vals, 1);
});

function barHeight(val) {
    return Math.round((val / maxValue.value) * 180) + 'px';
}

const totalIncome  = computed(() => props.summaries.reduce((s, m) => s + m.income, 0));
const totalExpense = computed(() => props.summaries.reduce((s, m) => s + m.expense, 0));
const totalNet     = computed(() => totalIncome.value - totalExpense.value);
</script>

<template>
    <Head title="Monthly Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Monthly Reports</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Annual cash flow overview</p>
                </div>
                <Link
                    :href="route('reports.monthly', { month: new Date().getMonth() + 1, year: filters.year })"
                    class="flex items-center gap-2 px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Monthly Detail
                </Link>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-6">

            <!-- Year Filter -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
                <label class="text-sm font-medium text-slate-600">Year</label>
                <select
                    v-model="selectedYear"
                    class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
                    <option v-for="y in available_years" :key="y" :value="y">{{ y }}</option>
                </select>
                <button
                    @click="applyFilter"
                    class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    Apply
                </button>
            </div>

            <!-- Annual Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Total Income</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ formatIDR(totalIncome) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ filters.year }}</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Total Expenses</p>
                    <p class="text-2xl font-bold text-rose-600">{{ formatIDR(totalExpense) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ filters.year }}</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Net Cash Flow</p>
                    <p :class="['text-2xl font-bold', totalNet >= 0 ? 'text-emerald-600' : 'text-rose-600']">
                        {{ totalNet >= 0 ? '+' : '' }}{{ formatIDR(totalNet) }}
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ filters.year }}</p>
                </div>
            </div>

            <!-- Bar Chart -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-800 mb-6">Income vs Expenses</h2>

                <div class="flex items-end justify-between gap-1 h-48 px-2">
                    <div
                        v-for="m in summaries"
                        :key="m.month"
                        class="flex-1 flex flex-col items-center gap-1 group"
                    >
                        <!-- Bars -->
                        <div class="flex items-end gap-0.5 h-44 relative">
                            <!-- Income bar -->
                            <div
                                class="w-3 sm:w-4 rounded-t bg-emerald-400 hover:bg-emerald-500 transition-all duration-500"
                                :style="{ height: barHeight(m.income) }"
                                :title="'Income: ' + formatIDR(m.income)"
                            />
                            <!-- Expense bar -->
                            <div
                                class="w-3 sm:w-4 rounded-t bg-rose-400 hover:bg-rose-500 transition-all duration-500"
                                :style="{ height: barHeight(m.expense) }"
                                :title="'Expense: ' + formatIDR(m.expense)"
                            />
                        </div>
                        <!-- Month label -->
                        <span class="text-xs text-slate-400">{{ m.label }}</span>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex items-center gap-4 mt-4 justify-center">
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-sm bg-emerald-400" />
                        <span class="text-xs text-slate-500">Income</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-sm bg-rose-400" />
                        <span class="text-xs text-slate-500">Expenses</span>
                    </div>
                </div>
            </div>

            <!-- Monthly Summary Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-800">Monthly Breakdown</h2>
                </div>
                <div class="overflow-x-auto">
                    <EasyDataTable
                        :headers="headers"
                        :items="summaries"
                        table-class-name="customize-table"
                        theme-color="#10b981"
                    >
                        <template #item-month="m">
                            <span class="font-medium text-slate-800">{{ m.label }} {{ filters.year }}</span>
                        </template>
                        <template #item-income="{ income }">
                            <span class="text-emerald-600 font-medium">{{ formatIDR(income) }}</span>
                        </template>
                        <template #item-expense="{ expense }">
                            <span class="text-rose-600 font-medium">{{ formatIDR(expense) }}</span>
                        </template>
                        <template #item-net="{ net }">
                            <span :class="['font-semibold', net >= 0 ? 'text-emerald-600' : 'text-rose-600']">
                                {{ net >= 0 ? '+' : '' }}{{ formatIDR(net) }}
                            </span>
                        </template>
                        <template #item-detail="m">
                            <Link
                                :href="route('reports.monthly', { month: m.month, year: filters.year })"
                                class="text-xs text-emerald-600 hover:text-emerald-800 font-medium"
                            >
                                View →
                            </Link>
                        </template>
                    </EasyDataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
