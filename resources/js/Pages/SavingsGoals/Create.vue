<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    target_amount: '',
    current_amount: 0,
    target_date: '',
});

const submit = () => {
    form.post(route('savings-goals.store'));
};
</script>

<template>
    <Head title="New Savings Goal" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('savings-goals.index')"
                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">New Savings Goal</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Define a financial target to work towards</p>
                </div>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <div class="max-w-lg">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Goal Name" class="mb-1.5" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. New Laptop, Emergency Fund"
                                class="w-full"
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-1.5" />
                        </div>

                        <!-- Target Amount -->
                        <div>
                            <InputLabel for="target_amount" value="Target Amount (IDR)" class="mb-1.5" />
                            <TextInput
                                id="target_amount"
                                v-model="form.target_amount"
                                type="number"
                                min="1"
                                step="any"
                                placeholder="e.g. 15000000"
                                class="w-full"
                            />
                            <InputError :message="form.errors.target_amount" class="mt-1.5" />
                        </div>

                        <!-- Current Amount -->
                        <div>
                            <InputLabel for="current_amount" value="Already Saved (IDR)" class="mb-1.5" />
                            <TextInput
                                id="current_amount"
                                v-model="form.current_amount"
                                type="number"
                                min="0"
                                step="any"
                                placeholder="0"
                                class="w-full"
                            />
                            <InputError :message="form.errors.current_amount" class="mt-1.5" />
                        </div>

                        <!-- Target Date -->
                        <div>
                            <InputLabel for="target_date" value="Target Date (optional)" class="mb-1.5" />
                            <TextInput
                                id="target_date"
                                v-model="form.target_date"
                                type="date"
                                class="w-full"
                            />
                            <InputError :message="form.errors.target_date" class="mt-1.5" />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Goal' }}
                            </button>
                            <Link
                                :href="route('savings-goals.index')"
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
