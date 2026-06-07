<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    recurringTransactions: Object,
    accounts: Array,
    categories: Array,
});

// ── Modal state ───────────────────────────────────────────
const showModal = ref(false);
const editingItem = ref(null);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    account_id: '',
    category_id: '',
    sub_category_id: '',
    type: 'expense',
    amount: '',
    description: '',
    classification: 'need',
    frequency: 'monthly',
    start_date: today,
    end_date: '',
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
    editingItem.value = null;
    form.reset();
    form.start_date = today;
    form.classification = 'need';
    form.frequency = 'monthly';
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (item) => {
    editingItem.value = item;
    form.account_id = item.account_id;
    form.category_id = item.category_id;
    form.sub_category_id = item.sub_category_id || '';
    form.type = item.type;
    form.amount = item.amount;
    form.description = item.description || '';
    form.classification = item.classification || 'need';
    form.frequency = item.frequency;
    form.start_date = item.start_date?.split('T')[0] || today;
    form.end_date = item.end_date?.split('T')[0] || '';
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
};

const submit = () => {
    if (editingItem.value) {
        form.put(route('recurring-transactions.update', editingItem.value.id), { onSuccess: closeModal });
    } else {
        form.post(route('recurring-transactions.store'), { onSuccess: closeModal });
    }
};

// ── Confirm Dialog ────────────────────────────────────────
const dialog = ref({ show: false, title: '', message: '', type: 'danger', resolve: null });

function confirmAction(options) {
    return new Promise((resolve) => {
        dialog.value = { ...options, show: true, resolve };
    });
}

function onDialogConfirm() { dialog.value.show = false; dialog.value.resolve?.(true); }
function onDialogCancel()  { dialog.value.show = false; dialog.value.resolve?.(false); }

const deleteItem = async (id) => {
    const ok = await confirmAction({
        title: 'Delete Recurring Transaction?',
        message: 'Future auto-generated entries will stop. Past transactions are not affected.',
        type: 'danger',
        confirmText: 'Delete',
    });
    if (ok) router.delete(route('recurring-transactions.destroy', id));
};

// ── Helpers ───────────────────────────────────────────────
const formatCurrency = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount || 0);

const formatDate = (date) =>
    date ? new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';

const statusBadge = (item) => {
    if (!item.is_active) return { label: 'Inactive', class: 'bg-slate-100 text-slate-600' };
    if (item.end_date && new Date(item.end_date) < new Date()) return { label: 'Expired', class: 'bg-rose-100 text-rose-600' };
    return { label: 'Active', class: 'bg-emerald-100 text-emerald-700' };
};
</script>

<template>
    <Head title="Recurring Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Recurring Transactions</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Automatically generated transactions on schedule</p>
                </div>
                <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Recurring
                </button>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-4">
            <!-- Table -->
            <div v-if="recurringTransactions?.data?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Description</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Category</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Account</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Frequency</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Next Due</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in recurringTransactions.data" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 text-slate-800 max-w-48 truncate">{{ item.description || '—' }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="text-slate-700">{{ item.category?.name }}</span>
                                    <span v-if="item.sub_category" class="text-xs text-slate-400 block">{{ item.sub_category.name }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-slate-600">{{ item.account?.name }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 capitalize">{{ item.frequency }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap">{{ formatDate(item.next_due_date) }}</td>
                                <td class="px-6 py-3.5">
                                    <span :class="['text-xs font-medium px-2.5 py-1 rounded-full', statusBadge(item).class]">{{ statusBadge(item).label }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <span :class="['font-semibold', item.type === 'income' ? 'text-blue-600' : 'text-rose-600']">
                                        {{ item.type === 'income' ? '+' : '-' }}{{ formatCurrency(item.amount) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(item)" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Edit</button>
                                        <button @click="deleteItem(item.id)" class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div v-if="recurringTransactions.last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">Showing {{ recurringTransactions.from }}–{{ recurringTransactions.to }} of {{ recurringTransactions.total }}</p>
                    <div class="flex items-center gap-1">
                        <a v-for="link in recurringTransactions.links" :key="link.label" :href="link.url || '#'" :class="['px-3 py-1.5 text-xs font-medium rounded-lg transition-colors', link.active ? 'bg-emerald-600 text-white' : link.url ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed']" v-html="link.label" />
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm p-16 text-center">
                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900 mb-1">No recurring transactions</h3>
                <p class="text-sm text-slate-500 mb-4">Set up automatic transactions that repeat monthly.</p>
                <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">Add Recurring</button>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Recurring Transaction Modal -->
    <Modal
        :show="showModal"
        :title="editingItem ? 'Edit Recurring Transaction' : 'Add Recurring Transaction'"
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
                <InputLabel for="rec-account" value="Account" class="mb-1.5" />
                <select id="rec-account" v-model="form.account_id" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                    <option value="">Select account...</option>
                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
                <InputError :message="form.errors.account_id" class="mt-1.5" />
            </div>

            <!-- Category -->
            <div>
                <InputLabel for="rec-cat" value="Category" class="mb-1.5" />
                <select id="rec-cat" v-model="form.category_id" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                    <option value="">Select category...</option>
                    <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <InputError :message="form.errors.category_id" class="mt-1.5" />
            </div>

            <!-- Sub-category -->
            <div v-if="availableSubs.length">
                <InputLabel for="rec-sub" value="Sub-category (optional)" class="mb-1.5" />
                <select id="rec-sub" v-model="form.sub_category_id" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                    <option value="">None</option>
                    <option v-for="sub in availableSubs" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                </select>
                <InputError :message="form.errors.sub_category_id" class="mt-1.5" />
            </div>

            <!-- Amount + Frequency -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="rec-amount" value="Amount (IDR)" class="mb-1.5" />
                    <TextInput id="rec-amount" v-model="form.amount" type="number" min="0.01" step="0.01" placeholder="0" class="w-full" />
                    <InputError :message="form.errors.amount" class="mt-1.5" />
                </div>
                <div>
                    <InputLabel for="rec-freq" value="Frequency" class="mb-1.5" />
                    <select id="rec-freq" v-model="form.frequency" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                        <option value="monthly">Monthly</option>
                    </select>
                    <InputError :message="form.errors.frequency" class="mt-1.5" />
                </div>
            </div>

            <!-- Start Date + End Date -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="rec-start" value="Start Date" class="mb-1.5" />
                    <TextInput id="rec-start" v-model="form.start_date" type="date" class="w-full" />
                    <InputError :message="form.errors.start_date" class="mt-1.5" />
                </div>
                <div>
                    <InputLabel for="rec-end" value="End Date (optional)" class="mb-1.5" />
                    <TextInput id="rec-end" v-model="form.end_date" type="date" class="w-full" />
                    <InputError :message="form.errors.end_date" class="mt-1.5" />
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
                <InputLabel for="rec-desc" value="Description (optional)" class="mb-1.5" />
                <textarea id="rec-desc" v-model="form.description" rows="2" placeholder="e.g. Monthly rent payment" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none" />
                <InputError :message="form.errors.description" class="mt-1.5" />
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                    {{ form.processing ? 'Saving...' : (editingItem ? 'Save Changes' : 'Create Recurring') }}
                </button>
                <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            </div>
        </form>
    </Modal>

    <ConfirmDialog
        :show="dialog.show"
        :title="dialog.title"
        :message="dialog.message"
        :type="dialog.type"
        :confirm-text="dialog.confirmText ?? 'Delete'"
        @confirm="onDialogConfirm"
        @cancel="onDialogCancel"
    />
</template>
