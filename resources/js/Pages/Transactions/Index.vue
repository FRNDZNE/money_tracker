<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    transactions: Object,
    accounts: Array,
    categories: Array,
    filters: Object,
});

// ── Modal state ───────────────────────────────────────────
const showModal = ref(false);
const editingTransaction = ref(null);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    account_id: '',
    category_id: '',
    sub_category_id: '',
    type: 'expense',
    amount: '',
    description: '',
    transaction_date: today,
    classification: 'need',
});

const filteredCategories = computed(() =>
    props.categories?.filter((c) => c.type === form.type) || [],
);

const availableSubs = computed(() => {
    const cat = props.categories?.find((c) => c.id == form.category_id);
    return cat?.sub_categories || [];
});

watch(() => form.type, () => {
    form.category_id = '';
    form.sub_category_id = '';
});

watch(() => form.category_id, (nv, ov) => {
    if (nv !== ov) form.sub_category_id = '';
});

const openCreate = () => {
    editingTransaction.value = null;
    form.reset();
    form.transaction_date = today;
    form.classification = 'need';
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (tx) => {
    editingTransaction.value = tx;
    form.account_id = tx.account_id;
    form.category_id = tx.category_id;
    form.sub_category_id = tx.sub_category_id || '';
    form.type = tx.type;
    form.amount = tx.amount;
    form.description = tx.description || '';
    form.transaction_date = tx.transaction_date?.split('T')[0] || today;
    form.classification = tx.classification || 'need';
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingTransaction.value = null;
};

const submit = () => {
    if (editingTransaction.value) {
        form.put(route('transactions.update', editingTransaction.value.id), { onSuccess: closeModal });
    } else {
        form.post(route('transactions.store'), { onSuccess: closeModal });
    }
};

// ── Filters ───────────────────────────────────────────────
const filterType = ref(props.filters?.type || '');
const filterAccount = ref(props.filters?.account_id || '');
const filterMonth = ref(props.filters?.month || '');
const filterYear = ref(props.filters?.year || new Date().getFullYear().toString());

const applyFilters = () => {
    router.get(route('transactions.index'), {
        type: filterType.value || undefined,
        account_id: filterAccount.value || undefined,
        month: filterMonth.value || undefined,
        year: filterYear.value || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    filterType.value = '';
    filterAccount.value = '';
    filterMonth.value = '';
    filterYear.value = new Date().getFullYear().toString();
    router.get(route('transactions.index'));
};

const deleteTransaction = (id) => {
    if (confirm('Delete this transaction?')) {
        router.delete(route('transactions.destroy', id));
    }
};

// ── Helpers ───────────────────────────────────────────────
const formatCurrency = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount || 0);

const formatDate = (date) =>
    new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

const classificationBadge = (cls) =>
    ({ need: 'bg-blue-100 text-blue-700', want: 'bg-amber-100 text-amber-700', investment: 'bg-emerald-100 text-emerald-700' }[cls] || '');

const months = [
    { value: '1', label: 'January' }, { value: '2', label: 'February' },
    { value: '3', label: 'March' }, { value: '4', label: 'April' },
    { value: '5', label: 'May' }, { value: '6', label: 'June' },
    { value: '7', label: 'July' }, { value: '8', label: 'August' },
    { value: '9', label: 'September' }, { value: '10', label: 'October' },
    { value: '11', label: 'November' }, { value: '12', label: 'December' },
];
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Transactions</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ transactions?.total || 0 }} transactions</p>
                </div>
                <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Transaction
                </button>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-4">
            <!-- Filters -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-500">Type</label>
                        <select v-model="filterType" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                            <option value="">All Types</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-500">Account</label>
                        <select v-model="filterAccount" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                            <option value="">All Accounts</option>
                            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-500">Month</label>
                        <select v-model="filterMonth" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                            <option value="">All Months</option>
                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-slate-500">Year</label>
                        <select v-model="filterYear" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                            <option value="">All Years</option>
                            <option v-for="y in [2024, 2025, 2026, 2027]" :key="y" :value="y.toString()">{{ y }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="applyFilters" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">Apply</button>
                        <button @click="resetFilters" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Reset</button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div v-if="transactions?.data?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[800px]">
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
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap">{{ formatDate(tx.transaction_date) }}</td>
                                <td class="px-6 py-3.5 text-slate-800 max-w-48 truncate">{{ tx.description || '—' }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="text-slate-700">{{ tx.category?.name }}</span>
                                    <span v-if="tx.sub_category" class="text-xs text-slate-400 block">{{ tx.sub_category.name }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-slate-600">{{ tx.account?.name }}</td>
                                <td class="px-6 py-3.5">
                                    <span v-if="tx.classification" :class="['text-xs font-medium px-2.5 py-1 rounded-full capitalize', classificationBadge(tx.classification)]">{{ tx.classification }}</span>
                                    <span v-else class="text-slate-300">—</span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <span :class="['font-semibold', tx.type === 'income' ? 'text-blue-600' : 'text-rose-600']">
                                        {{ tx.type === 'income' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(tx)" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Edit</button>
                                        <button @click="deleteTransaction(tx.id)" class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="transactions.last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">Showing {{ transactions.from }}–{{ transactions.to }} of {{ transactions.total }}</p>
                    <div class="flex items-center gap-1">
                        <a v-for="link in transactions.links" :key="link.label" :href="link.url || '#'" :class="['px-3 py-1.5 text-xs font-medium rounded-lg transition-colors', link.active ? 'bg-emerald-600 text-white' : link.url ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed']" v-html="link.label" />
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
                <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">Add Transaction</button>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Transaction Modal -->
    <Modal
        :show="showModal"
        :title="editingTransaction ? 'Edit Transaction' : 'Add Transaction'"
        max-width="xl"
        @close="closeModal"
    >
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Type -->
            <div>
                <InputLabel value="Type" class="mb-1.5" />
                <div class="grid grid-cols-2 gap-3">
                    <label v-for="opt in [{ value: 'expense', label: 'Expense', icon: '📤' }, { value: 'income', label: 'Income', icon: '📥' }]" :key="opt.value"
                        :class="['flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all', form.type === opt.value ? (opt.value === 'expense' ? 'border-rose-500 bg-rose-50' : 'border-blue-500 bg-blue-50') : 'border-slate-200 hover:border-slate-300']">
                        <input type="radio" v-model="form.type" :value="opt.value" class="sr-only" />
                        <span class="text-xl">{{ opt.icon }}</span>
                        <span :class="['text-sm font-semibold', form.type === opt.value ? (opt.value === 'expense' ? 'text-rose-700' : 'text-blue-700') : 'text-slate-700']">{{ opt.label }}</span>
                    </label>
                </div>
                <InputError :message="form.errors.type" class="mt-1.5" />
            </div>

            <!-- Account -->
            <div>
                <InputLabel for="tx-account" value="Account" class="mb-1.5" />
                <select id="tx-account" v-model="form.account_id" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                    <option value="">Select account...</option>
                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
                <InputError :message="form.errors.account_id" class="mt-1.5" />
            </div>

            <!-- Category -->
            <div>
                <InputLabel for="tx-cat" value="Category" class="mb-1.5" />
                <select id="tx-cat" v-model="form.category_id" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                    <option value="">Select category...</option>
                    <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <InputError :message="form.errors.category_id" class="mt-1.5" />
            </div>

            <!-- Sub-category -->
            <div v-if="availableSubs.length">
                <InputLabel for="tx-sub" value="Sub-category (optional)" class="mb-1.5" />
                <select id="tx-sub" v-model="form.sub_category_id" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                    <option value="">None</option>
                    <option v-for="sub in availableSubs" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                </select>
                <InputError :message="form.errors.sub_category_id" class="mt-1.5" />
            </div>

            <!-- Amount + Date -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="tx-amount" value="Amount (IDR)" class="mb-1.5" />
                    <TextInput id="tx-amount" v-model="form.amount" type="number" min="0.01" step="0.01" placeholder="0" class="w-full" />
                    <InputError :message="form.errors.amount" class="mt-1.5" />
                </div>
                <div>
                    <InputLabel for="tx-date" value="Date" class="mb-1.5" />
                    <TextInput id="tx-date" v-model="form.transaction_date" type="date" class="w-full" />
                    <InputError :message="form.errors.transaction_date" class="mt-1.5" />
                </div>
            </div>

            <!-- Classification (expense only) -->
            <div v-if="form.type === 'expense'">
                <InputLabel value="Spending Classification" class="mb-1.5" />
                <div class="grid grid-cols-3 gap-2">
                    <label v-for="opt in [{ value: 'need', label: 'Need', icon: '🛒', desc: 'Essential' }, { value: 'want', label: 'Want', icon: '🎮', desc: 'Optional' }, { value: 'investment', label: 'Investment', icon: '📈', desc: 'Growth' }]" :key="opt.value"
                        :class="['flex flex-col items-center gap-1 p-2.5 rounded-lg border-2 cursor-pointer transition-all', form.classification === opt.value ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 hover:border-slate-300']">
                        <input type="radio" v-model="form.classification" :value="opt.value" class="sr-only" />
                        <span class="text-lg">{{ opt.icon }}</span>
                        <span :class="['text-xs font-semibold', form.classification === opt.value ? 'text-emerald-700' : 'text-slate-600']">{{ opt.label }}</span>
                        <span class="text-xs text-slate-400">{{ opt.desc }}</span>
                    </label>
                </div>
                <InputError :message="form.errors.classification" class="mt-1.5" />
            </div>

            <!-- Description -->
            <div>
                <InputLabel for="tx-desc" value="Description (optional)" class="mb-1.5" />
                <textarea id="tx-desc" v-model="form.description" rows="2" placeholder="What was this for?" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none" />
                <InputError :message="form.errors.description" class="mt-1.5" />
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                    {{ form.processing ? 'Saving...' : (editingTransaction ? 'Save Changes' : 'Save Transaction') }}
                </button>
                <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            </div>
        </form>
    </Modal>
</template>
