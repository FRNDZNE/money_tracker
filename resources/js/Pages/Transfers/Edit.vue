<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    transfer: Object,
    accounts: Array,
});

const form = useForm({
    from_account_id: props.transfer.from_account_id,
    to_account_id: props.transfer.to_account_id,
    amount: props.transfer.amount,
    transfer_date: props.transfer.transfer_date
        ? props.transfer.transfer_date.split('T')[0]
        : '',
    note: props.transfer.note || '',
});

const submit = () => {
    form.put(route('transfers.update', props.transfer.id));
};
</script>

<template>
    <Head title="Edit Transfer" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('transfers.index')"
                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit Transfer</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Update transfer details</p>
                </div>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <div class="max-w-lg">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- From / To accounts -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="from_account_id" value="From Account" class="mb-1.5" />
                                <select
                                    id="from_account_id"
                                    v-model="form.from_account_id"
                                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                                >
                                    <option value="">Select...</option>
                                    <option
                                        v-for="acc in accounts"
                                        :key="acc.id"
                                        :value="acc.id"
                                        :disabled="acc.id == form.to_account_id"
                                    >
                                        {{ acc.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.from_account_id" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel for="to_account_id" value="To Account" class="mb-1.5" />
                                <select
                                    id="to_account_id"
                                    v-model="form.to_account_id"
                                    class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                                >
                                    <option value="">Select...</option>
                                    <option
                                        v-for="acc in accounts"
                                        :key="acc.id"
                                        :value="acc.id"
                                        :disabled="acc.id == form.from_account_id"
                                    >
                                        {{ acc.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.to_account_id" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Amount and Date -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="amount" value="Amount (IDR)" class="mb-1.5" />
                                <TextInput id="amount" v-model="form.amount" type="number" min="0.01" step="1000" class="w-full" />
                                <InputError :message="form.errors.amount" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel for="transfer_date" value="Date" class="mb-1.5" />
                                <TextInput id="transfer_date" v-model="form.transfer_date" type="date" class="w-full" />
                                <InputError :message="form.errors.transfer_date" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Note -->
                        <div>
                            <InputLabel for="note" value="Note (optional)" class="mb-1.5" />
                            <textarea
                                id="note"
                                v-model="form.note"
                                rows="2"
                                placeholder="Reason for transfer..."
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none"
                            />
                            <InputError :message="form.errors.note" class="mt-1.5" />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                            <Link
                                :href="route('transfers.index')"
                                class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
