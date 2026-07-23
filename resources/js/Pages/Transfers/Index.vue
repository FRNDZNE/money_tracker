<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    transfers: Object,
    accounts: Array,
});

const headers = [
    { text: "Date", value: "transfer_date" },
    { text: "From", value: "from_account" },
    { text: "To", value: "to_account" },
    { text: "Amount", value: "amount" },
    { text: "Note", value: "note" },
    { text: "Actions", value: "actions" },
];

// ── Modal state ───────────────────────────────────────────
const showModal = ref(false);
const editingTransfer = ref(null);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    from_account_id: '',
    to_account_id: '',
    amount: '',
    transfer_date: today,
    note: '',
});

const previewFrom = computed(() => props.accounts?.find((a) => a.id == form.from_account_id)?.name);
const previewTo = computed(() => props.accounts?.find((a) => a.id == form.to_account_id)?.name);

const openCreate = () => {
    editingTransfer.value = null;
    form.reset();
    form.transfer_date = today;
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (transfer) => {
    editingTransfer.value = transfer;
    form.from_account_id = transfer.from_account_id;
    form.to_account_id = transfer.to_account_id;
    form.amount = transfer.amount;
    form.transfer_date = transfer.transfer_date?.split('T')[0] || today;
    form.note = transfer.note || '';
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingTransfer.value = null;
};

const submit = () => {
    if (editingTransfer.value) {
        form.put(route('transfers.update', editingTransfer.value.id), { onSuccess: closeModal });
    } else {
        form.post(route('transfers.store'), { onSuccess: closeModal });
    }
};

// ── Helpers ───────────────────────────────────────────────
const formatCurrency = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount || 0);

const formatDate = (date) =>
    new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

// ── Confirm Dialog ────────────────────────────────────────
const dialog = ref({ show: false, title: '', message: '', type: 'danger', resolve: null });

function confirmAction(options) {
    return new Promise((resolve) => {
        dialog.value = { ...options, show: true, resolve };
    });
}

function onDialogConfirm() { dialog.value.show = false; dialog.value.resolve?.(true); }
function onDialogCancel()  { dialog.value.show = false; dialog.value.resolve?.(false); }

const deleteTransfer = async (id) => {
    const ok = await confirmAction({
        title: 'Delete Transfer?',
        message: 'This will reverse the account balances affected by this transfer.',
        type: 'danger',
        confirmText: 'Delete',
    });
    if (ok) router.delete(route('transfers.destroy', id));
};
</script>

<template>
    <Head title="Transfers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Transfers</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Money movements between accounts</p>
                </div>
                <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Transfer
                </button>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <div v-if="transfers?.data?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <EasyDataTable
                        :headers="headers"
                        :items="transfers.data"
                        table-class-name="customize-table"
                        theme-color="#10b981"
                        hide-footer
                    >
                        <template #item-transfer_date="{ transfer_date }">
                            <span class="text-slate-500">{{ formatDate(transfer_date) }}</span>
                        </template>
                        <template #item-from_account="{ from_account }">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-rose-100 rounded-full flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </div>
                                <span class="font-medium text-slate-800">{{ from_account?.name }}</span>
                            </div>
                        </template>
                        <template #item-to_account="{ to_account }">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l4-4m0 0l4 4m-4-4V3" />
                                    </svg>
                                </div>
                                <span class="font-medium text-slate-800">{{ to_account?.name }}</span>
                            </div>
                        </template>
                        <template #item-amount="{ amount }">
                            <span class="font-semibold text-slate-900">{{ formatCurrency(amount) }}</span>
                        </template>
                        <template #item-note="{ note }">
                            <span class="text-slate-500">{{ note || '—' }}</span>
                        </template>
                        <template #item-actions="transfer">
                            <div class="flex items-center gap-2">
                                <button @click="openEdit(transfer)" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Edit</button>
                                <button @click="deleteTransfer(transfer.id)" class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">Delete</button>
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
                <!-- Pagination -->
                <div v-if="transfers.last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">Showing {{ transfers.from }}–{{ transfers.to }} of {{ transfers.total }}</p>
                    <div class="flex items-center gap-1">
                        <a v-for="link in transfers.links" :key="link.label" :href="link.url || '#'" :class="['px-3 py-1.5 text-xs font-medium rounded-lg transition-colors', link.active ? 'bg-emerald-600 text-white' : link.url ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed']" v-html="link.label" />
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm p-16 text-center">
                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900 mb-1">No transfers yet</h3>
                <p class="text-sm text-slate-500 mb-4">Record a transfer to move money between your accounts.</p>
                <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">New Transfer</button>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Transfer Modal -->
    <Modal
        :show="showModal"
        :title="editingTransfer ? 'Edit Transfer' : 'New Transfer'"
        max-width="md"
        @close="closeModal"
    >
        <form @submit.prevent="submit" class="space-y-5">
            <!-- From / To -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="tf-from" value="From Account" class="mb-1.5" />
                    <select id="tf-from" v-model="form.from_account_id" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id" :disabled="acc.id == form.to_account_id">{{ acc.name }}</option>
                    </select>
                    <InputError :message="form.errors.from_account_id" class="mt-1.5" />
                </div>
                <div>
                    <InputLabel for="tf-to" value="To Account" class="mb-1.5" />
                    <select id="tf-to" v-model="form.to_account_id" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id" :disabled="acc.id == form.from_account_id">{{ acc.name }}</option>
                    </select>
                    <InputError :message="form.errors.to_account_id" class="mt-1.5" />
                </div>
            </div>

            <!-- Transfer preview -->
            <div v-if="previewFrom && previewTo" class="flex items-center justify-center gap-3 py-1 px-4 bg-slate-50 rounded-lg">
                <span class="text-sm font-medium text-slate-700">{{ previewFrom }}</span>
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
                <span class="text-sm font-medium text-slate-700">{{ previewTo }}</span>
            </div>

            <!-- Amount + Date -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="tf-amount" value="Amount (IDR)" class="mb-1.5" />
                    <TextInput id="tf-amount" v-model="form.amount" type="number" min="0.01" step="0.01" placeholder="0" class="w-full" />
                    <InputError :message="form.errors.amount" class="mt-1.5" />
                </div>
                <div>
                    <InputLabel for="tf-date" value="Date" class="mb-1.5" />
                    <TextInput id="tf-date" v-model="form.transfer_date" type="date" class="w-full" />
                    <InputError :message="form.errors.transfer_date" class="mt-1.5" />
                </div>
            </div>

            <!-- Note -->
            <div>
                <InputLabel for="tf-note" value="Note (optional)" class="mb-1.5" />
                <textarea id="tf-note" v-model="form.note" rows="2" placeholder="Reason for transfer..." class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none" />
                <InputError :message="form.errors.note" class="mt-1.5" />
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                    {{ form.processing ? 'Processing...' : (editingTransfer ? 'Save Changes' : 'Record Transfer') }}
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
