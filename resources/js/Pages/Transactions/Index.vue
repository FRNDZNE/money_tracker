<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transactions: Object, // paginated
    accounts: Array,
    categories: Array,
    filters: Object,
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

// Filters
const filterType = ref(props.filters?.type || '');
const filterAccount = ref(props.filters?.account_id || '');
const filterMonth = ref(props.filters?.month || '');
const filterYear = ref(props.filters?.year || new Date().getFullYear().toString());

const applyFilters = () => {
    router.get(
        route('transactions.index'),
        {
            type: filterType.value || undefined,
            account_id: filterAccount.value || undefined,
            month: filterMonth.value || undefined,
            year: filterYear.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const resetFilters = () => {
    filterType.value = '';
    filterAccount.value = '';
    filterMonth.value = '';
    filterYear.value = new Date().getFullYear().toString();
    router.get(route('transactions.index'), {}, { preserveState: false });
};

const deleteTransaction = (id) => {
    if (confirm('Delete this transaction?')) {
        router.delete(route('transactions.destroy', id));
    }
};

const classificationBadge = (cls) => {
    const map = {
        need: 'bg-blue-100 text-blue-700',
        want: 'bg-amber-100 text-amber-700',
        investment: 'bg-emerald-100 text-emerald-700',
    };
    return map[cls] || '';
};

const months = [
    { value: '1', label: 'January' },
    { value: '2', label: 'February' },
    { value: '3', label: 'March' },
    { value: '4', label: 'April' },
    { value: '5', label: 'May' },
    { value: '6', label: 'June' },
    { value: '7', label: 'July' },
    { value: '8', label: 'August' },
    { value: '9', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' },
];
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Transactions</h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ transactions?.total || 0 }} transactions
                    </p>
                </div>
                <Link
                    :href="route('transactions.create')"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Transaction
                </Link>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-4">
            <!-- Filters -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <!-- Type -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-500">Type</label>
                        <select
                            v-model="filterType"
                            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                        >
                            <option value="">All Types</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <!-- Account -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-500">Account</label>
                        <select
                            v-model="filterAccount"
                            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                        >
                            <option value="">All Accounts</option>
                            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                        </select>
                    </div>

                    <!-- Month -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-500">Month</label>
                        <select
                            v-model="filterMonth"
                            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                        >
                            <option value="">All Months</option>
                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                    </div>

                    <!-- Year -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-500">Year</label>
                        <select
                            v-model="filterYear"
                            class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                        >
                            <option value="">All Years</option>
                            <option v-for="y in [2024, 2025, 2026, 2027]" :key="y" :value="y.toString()">{{ y }}</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            Apply
                        </button>
                        <button
                            @click="resetFilters"
                            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div v-if="transactions?.data?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Description</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Category</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Account</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Classification</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="tx in transactions.data"
                            :key="tx.id"
                            class="hover:bg-slate-50 transition-colors"
                        >
                            <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap">{{ formatDate(tx.transaction_date) }}</td>
                            <td class="px-6 py-3.5 text-slate-800 max-w-48 truncate">{{ tx.description || '—' }}</td>
                            <td class="px-6 py-3.5">
                                <div>
                                    <span class="text-slate-700">{{ tx.category?.name }}</span>
                                    <span v-if="tx.sub_category" class="text-xs text-slate-400 block">{{ tx.sub_category.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-slate-600">{{ tx.account?.name }}</td>
                            <td class="px-6 py-3.5">
                                <span
                                    v-if="tx.classification"
                                    :class="['text-xs font-medium px-2.5 py-1 rounded-full capitalize', classificationBadge(tx.classification)]"
                                >
                                    {{ tx.classification }}
                                </span>
                                <span v-else class="text-slate-300">—</span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <span :class="['font-semibold', tx.type === 'income' ? 'text-blue-600' : 'text-rose-600']">
                                    {{ tx.type === 'income' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('transactions.edit', tx.id)"
                                        class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteTransaction(tx.id)"
                                        class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="transactions.last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Showing {{ transactions.from }}–{{ transactions.to }} of {{ transactions.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in transactions.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                link.active
                                    ? 'bg-emerald-600 text-white'
                                    : link.url
                                        ? 'text-slate-600 hover:bg-slate-100'
                                        : 'text-slate-300 cursor-not-allowed',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm p-16 text-center">
                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900 mb-1">No transactions found</h3>
                <p class="text-sm text-slate-500 mb-4">Try adjusting your filters or add a new transaction.</p>
                <Link
                    :href="route('transactions.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    Add Transaction
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
