<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    type: 'cash',
    initial_balance: 0,
    is_active: true,
});

const submit = () => {
    form.post(route('accounts.store'));
};
</script>

<template>
    <Head title="Add Account" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('accounts.index')"
                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Add Account</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Create a new account to track your money</p>
                </div>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <div class="max-w-lg">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Account Name" class="mb-1.5" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. BCA, OVO, Wallet"
                                class="w-full"
                                autofocus
                            />
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
                                        form.type === opt.value
                                            ? 'border-emerald-500 bg-emerald-50'
                                            : 'border-slate-200 hover:border-slate-300',
                                    ]"
                                >
                                    <input type="radio" v-model="form.type" :value="opt.value" class="sr-only" />
                                    <span class="text-xl">{{ opt.icon }}</span>
                                    <span :class="['text-xs font-medium', form.type === opt.value ? 'text-emerald-700' : 'text-slate-600']">
                                        {{ opt.label }}
                                    </span>
                                </label>
                            </div>
                            <InputError :message="form.errors.type" class="mt-1.5" />
                        </div>

                        <!-- Initial Balance -->
                        <div>
                            <InputLabel for="initial_balance" value="Initial Balance (IDR)" class="mb-1.5" />
                            <TextInput
                                id="initial_balance"
                                v-model="form.initial_balance"
                                type="number"
                                min="0"
                                step="1000"
                                placeholder="0"
                                class="w-full"
                            />
                            <InputError :message="form.errors.initial_balance" class="mt-1.5" />
                        </div>

                        <!-- Active status -->
                        <div class="flex items-center gap-3">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500"
                            />
                            <label for="is_active" class="text-sm font-medium text-slate-700">Mark as active account</label>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Account' }}
                            </button>
                            <Link
                                :href="route('accounts.index')"
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
