<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    report: Object,
    available_years: Array,
    filters: Object,
});

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const selectedMonth = ref(props.filters.month);
const selectedYear  = ref(props.filters.year);

function applyFilter() {
    router.get(route('reports.monthly'), {
        month: selectedMonth.value,
        year: selectedYear.value,
    }, { preserveState: true, replace: true });
}

const formatIDR = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);

const totalExpense = computed(() => props.report.total_expense);
</script>

<template>
    <Head title="Monthly Detail" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('reports.index', { year: filters.year })"
                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">
                        {{ months[filters.month - 1] }} {{ filters.year }}
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">Detailed monthly report</p>
                </div>
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
                        <option v-for="y in available_years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <button
                    @click="applyFilter"
                    class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    Apply
                </button>
                <div class="ml-auto flex items-center gap-2">
                    <a :href="route('export.reports.monthly.excel', { month: selectedMonth, year: selectedYear })" class="flex items-center gap-1.5 px-3 py-1.5 border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Excel
                    </a>
                    <a :href="route('export.reports.monthly.pdf', { month: selectedMonth, year: selectedYear })" class="flex items-center gap-1.5 px-3 py-1.5 border border-rose-300 text-rose-600 hover:bg-rose-50 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        PDF
                    </a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Total Income</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ formatIDR(report.total_income) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Total Expenses</p>
                    <p class="text-2xl font-bold text-rose-600">{{ formatIDR(report.total_expense) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wide mb-1">Net Cash Flow</p>
                    <p :class="['text-2xl font-bold', report.net >= 0 ? 'text-emerald-600' : 'text-rose-600']">
                        {{ report.net >= 0 ? '+' : '' }}{{ formatIDR(report.net) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Expense by Category -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="text-sm font-semibold text-slate-800">Expenses by Category</h2>
                    </div>
                    <div v-if="report.expense_categories.length === 0" class="px-6 py-10 text-center text-sm text-slate-500">
                        No expenses recorded this month.
                    </div>
                    <ul v-else class="divide-y divide-slate-100">
                        <li v-for="cat in report.expense_categories" :key="cat.category_id" class="px-6 py-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm font-medium text-slate-700">{{ cat.category_name }}</span>
                                <span class="text-sm font-semibold text-rose-600">{{ formatIDR(cat.total) }}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div
                                    class="h-1.5 rounded-full bg-rose-400"
                                    :style="{ width: totalExpense > 0 ? Math.round((cat.total / totalExpense) * 100) + '%' : '0%' }"
                                />
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ totalExpense > 0 ? Math.round((cat.total / totalExpense) * 100) : 0 }}% · {{ cat.count }} transaction(s)
                            </p>
                        </li>
                    </ul>
                </div>

                <!-- Income by Category -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="text-sm font-semibold text-slate-800">Income by Category</h2>
                    </div>
                    <div v-if="report.income_categories.length === 0" class="px-6 py-10 text-center text-sm text-slate-500">
                        No income recorded this month.
                    </div>
                    <ul v-else class="divide-y divide-slate-100">
                        <li v-for="cat in report.income_categories" :key="cat.category_id" class="px-6 py-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm font-medium text-slate-700">{{ cat.category_name }}</span>
                                <span class="text-sm font-semibold text-emerald-600">{{ formatIDR(cat.total) }}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div
                                    class="h-1.5 rounded-full bg-emerald-400"
                                    :style="{ width: report.total_income > 0 ? Math.round((cat.total / report.total_income) * 100) + '%' : '0%' }"
                                />
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ report.total_income > 0 ? Math.round((cat.total / report.total_income) * 100) : 0 }}% · {{ cat.count }} transaction(s)
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
