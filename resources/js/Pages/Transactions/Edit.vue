<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    transaction: Object,
    accounts: Array,
    categories: Array,
});

const form = useForm({
    account_id: props.transaction.account_id,
    category_id: props.transaction.category_id,
    sub_category_id: props.transaction.sub_category_id || '',
    type: props.transaction.type,
    amount: props.transaction.amount,
    description: props.transaction.description || '',
    transaction_date: props.transaction.transaction_date
        ? props.transaction.transaction_date.split('T')[0]
        : '',
    classification: props.transaction.classification || 'need',
});

const filteredCategories = computed(() => {
    return props.categories?.filter((c) => c.type === form.type) || [];
});

const availableSubCategories = computed(() => {
    const cat = props.categories?.find((c) => c.id == form.category_id);
    return cat?.sub_categories || [];
});

watch(() => form.type, () => {
    form.category_id = '';
    form.sub_category_id = '';
});

watch(() => form.category_id, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        form.sub_category_id = '';
    }
});

const submit = () => {
    form.put(route('transactions.update', props.transaction.id));
};
</script>

<template>
    <Head title="Edit Transaction" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('transactions.index')"
                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit Transaction</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Update transaction details</p>
                </div>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <div class="max-w-xl">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Type selector -->
                        <div>
                            <InputLabel value="Transaction Type" class="mb-1.5" />
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    v-for="opt in [
                                        { value: 'expense', label: 'Expense', icon: '📤' },
                                        { value: 'income', label: 'Income', icon: '📥' },
                                    ]"
                                    :key="opt.value"
                                    :class="[
                                        'flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all',
                                        form.type === opt.value
                                            ? opt.value === 'expense'
                                                ? 'border-rose-500 bg-rose-50'
                                                : 'border-blue-500 bg-blue-50'
                                            : 'border-slate-200 hover:border-slate-300',
                                    ]"
                                >
                                    <input type="radio" v-model="form.type" :value="opt.value" class="sr-only" />
                                    <span class="text-xl">{{ opt.icon }}</span>
                                    <span :class="['text-sm font-semibold', form.type === opt.value ? (opt.value === 'expense' ? 'text-rose-700' : 'text-blue-700') : 'text-slate-700']">
                                        {{ opt.label }}
                                    </span>
                                </label>
                            </div>
                            <InputError :message="form.errors.type" class="mt-1.5" />
                        </div>

                        <!-- Account -->
                        <div>
                            <InputLabel for="account_id" value="Account" class="mb-1.5" />
                            <select
                                id="account_id"
                                v-model="form.account_id"
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                            >
                                <option value="">Select account...</option>
                                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                            </select>
                            <InputError :message="form.errors.account_id" class="mt-1.5" />
                        </div>

                        <!-- Category -->
                        <div>
                            <InputLabel for="category_id" value="Category" class="mb-1.5" />
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                            >
                                <option value="">Select category...</option>
                                <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <InputError :message="form.errors.category_id" class="mt-1.5" />
                        </div>

                        <!-- Sub-category -->
                        <div v-if="availableSubCategories.length">
                            <InputLabel for="sub_category_id" value="Sub-category (optional)" class="mb-1.5" />
                            <select
                                id="sub_category_id"
                                v-model="form.sub_category_id"
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                            >
                                <option value="">None</option>
                                <option v-for="sub in availableSubCategories" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
                            </select>
                            <InputError :message="form.errors.sub_category_id" class="mt-1.5" />
                        </div>

                        <!-- Amount and Date -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="amount" value="Amount (IDR)" class="mb-1.5" />
                                <TextInput id="amount" v-model="form.amount" type="number" min="0.01" step="1000" class="w-full" />
                                <InputError :message="form.errors.amount" class="mt-1.5" />
                            </div>
                            <div>
                                <InputLabel for="transaction_date" value="Date" class="mb-1.5" />
                                <TextInput id="transaction_date" v-model="form.transaction_date" type="date" class="w-full" />
                                <InputError :message="form.errors.transaction_date" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Classification -->
                        <div v-if="form.type === 'expense'">
                            <InputLabel value="Spending Classification" class="mb-1.5" />
                            <div class="grid grid-cols-3 gap-2">
                                <label
                                    v-for="opt in [
                                        { value: 'need', label: 'Need', icon: '🛒' },
                                        { value: 'want', label: 'Want', icon: '🎮' },
                                        { value: 'investment', label: 'Investment', icon: '📈' },
                                    ]"
                                    :key="opt.value"
                                    :class="[
                                        'flex flex-col items-center gap-1 p-2.5 rounded-lg border-2 cursor-pointer transition-all',
                                        form.classification === opt.value
                                            ? 'border-emerald-500 bg-emerald-50'
                                            : 'border-slate-200 hover:border-slate-300',
                                    ]"
                                >
                                    <input type="radio" v-model="form.classification" :value="opt.value" class="sr-only" />
                                    <span class="text-lg">{{ opt.icon }}</span>
                                    <span :class="['text-xs font-semibold', form.classification === opt.value ? 'text-emerald-700' : 'text-slate-600']">
                                        {{ opt.label }}
                                    </span>
                                </label>
                            </div>
                            <InputError :message="form.errors.classification" class="mt-1.5" />
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" value="Description (optional)" class="mb-1.5" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="2"
                                placeholder="What was this for?"
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none"
                            />
                            <InputError :message="form.errors.description" class="mt-1.5" />
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
                                :href="route('transactions.index')"
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
