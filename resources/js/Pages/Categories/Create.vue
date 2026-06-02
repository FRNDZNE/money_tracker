<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    type: 'expense',
});

const submit = () => {
    form.post(route('categories.store'));
};
</script>

<template>
    <Head title="Add Category" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('categories.index')"
                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Add Category</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Create a new income or expense category</p>
                </div>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <div class="max-w-lg">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Category Name" class="mb-1.5" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. Food, Salary, Transportation"
                                class="w-full"
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-1.5" />
                        </div>

                        <!-- Type -->
                        <div>
                            <InputLabel value="Category Type" class="mb-1.5" />
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    v-for="opt in [
                                        { value: 'expense', label: 'Expense', icon: '📤', desc: 'Money going out' },
                                        { value: 'income', label: 'Income', icon: '📥', desc: 'Money coming in' },
                                    ]"
                                    :key="opt.value"
                                    :class="[
                                        'flex flex-col gap-1 p-4 rounded-lg border-2 cursor-pointer transition-all',
                                        form.type === opt.value
                                            ? opt.value === 'expense'
                                                ? 'border-rose-500 bg-rose-50'
                                                : 'border-blue-500 bg-blue-50'
                                            : 'border-slate-200 hover:border-slate-300',
                                    ]"
                                >
                                    <input type="radio" v-model="form.type" :value="opt.value" class="sr-only" />
                                    <span class="text-2xl">{{ opt.icon }}</span>
                                    <span :class="['text-sm font-semibold', form.type === opt.value ? (opt.value === 'expense' ? 'text-rose-700' : 'text-blue-700') : 'text-slate-700']">
                                        {{ opt.label }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ opt.desc }}</span>
                                </label>
                            </div>
                            <InputError :message="form.errors.type" class="mt-1.5" />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Category' }}
                            </button>
                            <Link
                                :href="route('categories.index')"
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
