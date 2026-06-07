<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    accounts: Array,
});

// ── Modal state ──────────────────────────────────────────
const showModal = ref(false);
const editingAccount = ref(null);

const form = useForm({
    name: '',
    type: 'cash',
    initial_balance: 0,
    is_active: true,
});

const openCreate = () => {
    editingAccount.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (account) => {
    editingAccount.value = account;
    form.name = account.name;
    form.type = account.type;
    form.initial_balance = account.initial_balance;
    form.is_active = account.is_active;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingAccount.value = null;
};

const submit = () => {
    if (editingAccount.value) {
        form.put(route('accounts.update', editingAccount.value.id), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route('accounts.store'), {
            onSuccess: closeModal,
        });
    }
};

// ── Helpers ───────────────────────────────────────────────
const formatCurrency = (amount) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount || 0);

const accountTypeBadge = (type) =>
    ({ cash: 'bg-amber-100 text-amber-700', bank: 'bg-blue-100 text-blue-700', e_wallet: 'bg-purple-100 text-purple-700' }[type] || 'bg-slate-100 text-slate-700');

const accountTypeLabel = (type) =>
    ({ cash: 'Cash', bank: 'Bank', e_wallet: 'E-Wallet' }[type] || type);

// ── Confirm Dialog ────────────────────────────────────────
const dialog = ref({ show: false, title: '', message: '', type: 'danger', resolve: null });

function confirmAction(options) {
    return new Promise((resolve) => {
        dialog.value = { ...options, show: true, resolve };
    });
}

function onDialogConfirm() { dialog.value.show = false; dialog.value.resolve?.(true); }
function onDialogCancel()  { dialog.value.show = false; dialog.value.resolve?.(false); }

const deleteAccount = async (account) => {
    const ok = await confirmAction({
        title: `Delete "${account.name}"?`,
        message: 'All transactions linked to this account will also be permanently deleted.',
        type: 'danger',
        confirmText: 'Delete Account',
    });
    if (ok) router.delete(route('accounts.destroy', account.id));
};
</script>

<template>
    <Head title="Accounts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Accounts</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Manage your money accounts</p>
                </div>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Account
                </button>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <div v-if="accounts?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Name</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Initial</th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Balance</th>
                                <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="account in accounts" :key="account.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ account.name }}</td>
                                <td class="px-6 py-4">
                                    <span :class="['text-xs font-medium px-2.5 py-1 rounded-full', accountTypeBadge(account.type)]">
                                        {{ accountTypeLabel(account.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-slate-500">{{ formatCurrency(account.initial_balance) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span :class="['font-semibold', account.balance >= 0 ? 'text-slate-900' : 'text-rose-600']">
                                        {{ formatCurrency(account.balance) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['text-xs font-medium px-2.5 py-1 rounded-full', account.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500']">
                                        {{ account.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(account)" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
                                            Edit
                                        </button>
                                        <button @click="deleteAccount(account)" class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm p-16 text-center">
                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900 mb-1">No accounts yet</h3>
                <p class="text-sm text-slate-500 mb-4">Create your first account to start tracking your money.</p>
                <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Add Account
                </button>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Account Modal -->
    <Modal
        :show="showModal"
        :title="editingAccount ? 'Edit Account' : 'Add Account'"
        max-width="md"
        @close="closeModal"
    >
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Name -->
            <div>
                <InputLabel for="acc-name" value="Account Name" class="mb-1.5" />
                <TextInput id="acc-name" v-model="form.name" type="text" placeholder="e.g. BCA, OVO, Wallet" class="w-full" autofocus />
                <InputError :message="form.errors.name" class="mt-1.5" />
            </div>

            <!-- Type -->
            <div>
                <InputLabel value="Account Type" class="mb-1.5" />
                <div class="grid grid-cols-3 gap-3">
                    <label
                        v-for="opt in [
                            { value: 'cash', label: 'Cash', icon: '💵' },
                            { value: 'bank', label: 'Bank', icon: '🏦' },
                            { value: 'e_wallet', label: 'E-Wallet', icon: '📱' },
                        ]"
                        :key="opt.value"
                        :class="[
                            'flex flex-col items-center gap-1.5 p-3 rounded-lg border-2 cursor-pointer transition-all',
                            form.type === opt.value ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 hover:border-slate-300',
                        ]"
                    >
                        <input type="radio" v-model="form.type" :value="opt.value" class="sr-only" />
                        <span class="text-xl">{{ opt.icon }}</span>
                        <span :class="['text-xs font-medium', form.type === opt.value ? 'text-emerald-700' : 'text-slate-600']">{{ opt.label }}</span>
                    </label>
                </div>
                <InputError :message="form.errors.type" class="mt-1.5" />
            </div>

            <!-- Initial Balance -->
            <div>
                <InputLabel for="acc-balance" value="Initial Balance (IDR)" class="mb-1.5" />
                <TextInput id="acc-balance" v-model="form.initial_balance" type="number" min="0" step="0.01" placeholder="0" class="w-full" />
                <InputError :message="form.errors.initial_balance" class="mt-1.5" />
            </div>

            <!-- Active status -->
            <div class="flex items-center gap-3">
                <input id="acc-active" v-model="form.is_active" type="checkbox" class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500" />
                <label for="acc-active" class="text-sm font-medium text-slate-700">Mark as active account</label>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                    {{ form.processing ? 'Saving...' : (editingAccount ? 'Save Changes' : 'Create Account') }}
                </button>
                <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
                    Cancel
                </button>
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
