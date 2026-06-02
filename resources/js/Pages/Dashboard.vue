<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    summary: Object,
    accountDistribution: Array,
    expenseByCategory: Array,
    spendingClassification: Object,
    cashFlowTrend: Array,
    recentTransactions: Array,
});

const formatCurrency = (amount) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount || 0);

const formatDate = (date) =>
    new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });

// Max value for expense bar chart
const maxExpense = computed(() =>
    props.expenseByCategory?.length
        ? Math.max(...props.expenseByCategory.map((c) => c.total))
        : 1,
);

// Total for spending classification percentage
const totalClassification = computed(() => {
    const s = props.spendingClassification || {};
    return (s.need || 0) + (s.want || 0) + (s.investment || 0) || 1;
});

// Max value for cash flow chart
const maxCashFlow = computed(() => {
    if (!props.cashFlowTrend?.length) return 1;
    return Math.max(...props.cashFlowTrend.flatMap((m) => [m.income, m.expense])) || 1;
});

const accountTypeLabel = (type) => {
    const map = { cash: 'Cash', bank: 'Bank', e_wallet: 'E-Wallet' };
    return map[type] || type;
};

const accountTypeColor = (type) => {
    const map = {
        cash: 'bg-amber-100 text-amber-700',
        bank: 'bg-blue-100 text-blue-700',
        e_wallet: 'bg-purple-100 text-purple-700',
    };
    return map[type] || 'bg-slate-100 text-slate-700';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Dashboard</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Financial overview for this month</p>
                </div>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <!-- Total Assets -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Assets</span>
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ formatCurrency(summary?.total_assets) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Across all active accounts</p>
                </div>

                <!-- Monthly Income -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Income This Month</span>
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(summary?.total_income) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Total income recorded</p>
                </div>

                <!-- Monthly Expenses -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Expenses This Month</span>
                        <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-rose-600">{{ formatCurrency(summary?.total_expense) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Total expenses recorded</p>
                </div>

                <!-- Net Cash Flow -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Net Cash Flow</span>
                        <div
                            :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center',
                                (summary?.net_cash_flow || 0) >= 0 ? 'bg-emerald-100' : 'bg-rose-100',
                            ]"
                        >
                            <svg
                                :class="[
                                    'w-4 h-4',
                                    (summary?.net_cash_flow || 0) >= 0 ? 'text-emerald-600' : 'text-rose-600',
                                ]"
                                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <p
                        :class="[
                            'text-2xl font-bold',
                            (summary?.net_cash_flow || 0) >= 0 ? 'text-emerald-600' : 'text-rose-600',
                        ]"
                    >
                        {{ formatCurrency(summary?.net_cash_flow) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Income minus expenses</p>
                </div>
            </div>

            <!-- Middle Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Account Distribution -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900 mb-4">Account Balances</h2>
                    <div v-if="accountDistribution?.length" class="space-y-3">
                        <div
                            v-for="account in accountDistribution"
                            :key="account.id"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span :class="['text-xs font-medium px-2 py-0.5 rounded-full shrink-0', accountTypeColor(account.type)]">
                                    {{ accountTypeLabel(account.type) }}
                                </span>
                                <span class="text-sm text-slate-700 truncate">{{ account.name }}</span>
                            </div>
                            <span
                                :class="[
                                    'text-sm font-semibold shrink-0 ml-2',
                                    account.balance >= 0 ? 'text-slate-900' : 'text-rose-600',
                                ]"
                            >
                                {{ formatCurrency(account.balance) }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <p class="text-sm text-slate-400">No accounts yet</p>
                        <a :href="route('accounts.create')" class="text-sm text-emerald-600 hover:underline mt-1 inline-block">Add an account →</a>
                    </div>
                </div>

                <!-- Spending Classification -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900 mb-4">Spending by Type</h2>
                    <div class="space-y-4">
                        <!-- Need -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium text-slate-600">🛒 Need</span>
                                <span class="text-xs font-semibold text-slate-800">{{ formatCurrency(spendingClassification?.need) }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-blue-500 rounded-full transition-all duration-700"
                                    :style="{ width: ((spendingClassification?.need || 0) / totalClassification * 100) + '%' }"
                                />
                            </div>
                        </div>
                        <!-- Want -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium text-slate-600">🎮 Want</span>
                                <span class="text-xs font-semibold text-slate-800">{{ formatCurrency(spendingClassification?.want) }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-amber-500 rounded-full transition-all duration-700"
                                    :style="{ width: ((spendingClassification?.want || 0) / totalClassification * 100) + '%' }"
                                />
                            </div>
                        </div>
                        <!-- Investment -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium text-slate-600">📈 Investment</span>
                                <span class="text-xs font-semibold text-slate-800">{{ formatCurrency(spendingClassification?.investment) }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-emerald-500 rounded-full transition-all duration-700"
                                    :style="{ width: ((spendingClassification?.investment || 0) / totalClassification * 100) + '%' }"
                                />
                            </div>
                        </div>
                        <p v-if="!totalClassification || totalClassification === 1" class="text-xs text-slate-400 text-center pt-2">No expense data this month</p>
                    </div>
                </div>

                <!-- Expenses by Category -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900 mb-4">Expenses by Category</h2>
                    <div v-if="expenseByCategory?.length" class="space-y-3">
                        <div
                            v-for="item in expenseByCategory.slice(0, 6)"
                            :key="item.category"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-slate-600 truncate">{{ item.category }}</span>
                                <span class="text-xs font-semibold text-slate-800 ml-2 shrink-0">{{ formatCurrency(item.total) }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-rose-500 rounded-full transition-all duration-700"
                                    :style="{ width: (item.total / maxExpense * 100) + '%' }"
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <p class="text-sm text-slate-400">No expenses this month</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                <!-- Cash Flow Trend -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm lg:col-span-2">
                    <h2 class="text-sm font-semibold text-slate-900 mb-4">Cash Flow (6 Months)</h2>
                    <div v-if="cashFlowTrend?.length" class="space-y-3">
                        <div
                            v-for="month in cashFlowTrend"
                            :key="month.month"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-slate-500 w-16 shrink-0">{{ month.month }}</span>
                                <div class="flex-1 mx-2 space-y-1">
                                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-400 rounded-full" :style="{ width: (month.income / maxCashFlow * 100) + '%' }" />
                                    </div>
                                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-rose-400 rounded-full" :style="{ width: (month.expense / maxCashFlow * 100) + '%' }" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 pt-1">
                            <div class="flex items-center gap-1.5">
                                <div class="w-2.5 h-2.5 rounded-sm bg-blue-400" />
                                <span class="text-xs text-slate-500">Income</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-2.5 h-2.5 rounded-sm bg-rose-400" />
                                <span class="text-xs text-slate-500">Expense</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <p class="text-sm text-slate-400">No data yet</p>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm lg:col-span-3 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">Recent Transactions</h2>
                        <a :href="route('transactions.index')" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">View all →</a>
                    </div>
                    <div v-if="recentTransactions?.length">
                        <div
                            v-for="tx in recentTransactions"
                            :key="tx.id"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 border-b border-slate-50 last:border-0 transition-colors"
                        >
                            <div
                                :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center shrink-0',
                                    tx.type === 'income' ? 'bg-blue-100' : 'bg-rose-100',
                                ]"
                            >
                                <svg
                                    :class="['w-4 h-4', tx.type === 'income' ? 'text-blue-600' : 'text-rose-600']"
                                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                >
                                    <path
                                        v-if="tx.type === 'income'"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 11l5-5m0 0l5 5m-5-5v12"
                                    />
                                    <path
                                        v-else
                                        stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 13l-5 5m0 0l-5-5m5 5V6"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">
                                    {{ tx.description || tx.category?.name || '—' }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ tx.account?.name }} · {{ formatDate(tx.transaction_date) }}
                                </p>
                            </div>
                            <span
                                :class="[
                                    'text-sm font-semibold shrink-0',
                                    tx.type === 'income' ? 'text-blue-600' : 'text-rose-600',
                                ]"
                            >
                                {{ tx.type === 'income' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-sm text-slate-400">No transactions yet</p>
                        <a :href="route('transactions.create')" class="text-sm text-emerald-600 hover:underline mt-1 inline-block">
                            Record your first transaction →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
