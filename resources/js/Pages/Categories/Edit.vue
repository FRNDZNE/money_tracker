<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    category: Object,
});

// Category form
const form = useForm({
    name: props.category.name,
    type: props.category.type,
});

const submit = () => {
    form.put(route('categories.update', props.category.id));
};

// Sub-category add form
const subForm = useForm({ name: '' });

const addSubCategory = () => {
    subForm.post(route('categories.sub-categories.store', props.category.id), {
        preserveScroll: true,
        onSuccess: () => subForm.reset(),
    });
};

// Sub-category inline edit
const editingSubId = ref(null);
const editSubForm = useForm({ name: '' });

const startEdit = (sub) => {
    editingSubId.value = sub.id;
    editSubForm.name = sub.name;
};

const cancelEdit = () => {
    editingSubId.value = null;
    editSubForm.reset();
};

const updateSubCategory = (sub) => {
    editSubForm.put(route('categories.sub-categories.update', [props.category.id, sub.id]), {
        preserveScroll: true,
        onSuccess: () => {
            editingSubId.value = null;
            editSubForm.reset();
        },
    });
};

const deleteSubCategory = (sub) => {
    if (confirm(`Delete sub-category "${sub.name}"?`)) {
        router.delete(route('categories.sub-categories.destroy', [props.category.id, sub.id]), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Edit Category" />

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
                    <h1 class="text-xl font-bold text-slate-900">Edit Category</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ category.name }}</p>
                </div>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-6">
            <div class="max-w-xl space-y-5">
                <!-- Category Form -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-slate-900 mb-4">Category Details</h2>
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="name" value="Category Name" class="mb-1.5" />
                            <TextInput id="name" v-model="form.name" type="text" class="w-full" autofocus />
                            <InputError :message="form.errors.name" class="mt-1.5" />
                        </div>
                        <div>
                            <InputLabel value="Category Type" class="mb-1.5" />
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
                                    <span class="text-lg">{{ opt.icon }}</span>
                                    <span :class="['text-sm font-medium', form.type === opt.value ? (opt.value === 'expense' ? 'text-rose-700' : 'text-blue-700') : 'text-slate-700']">
                                        {{ opt.label }}
                                    </span>
                                </label>
                            </div>
                            <InputError :message="form.errors.type" class="mt-1.5" />
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                            <Link :href="route('categories.index')" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>

                <!-- Sub-categories -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="text-sm font-semibold text-slate-900">Sub-categories</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Manage sub-categories for this category</p>
                    </div>

                    <!-- Existing sub-categories -->
                    <div v-if="category.sub_categories?.length" class="divide-y divide-slate-100">
                        <div
                            v-for="sub in category.sub_categories"
                            :key="sub.id"
                            class="flex items-center gap-3 px-6 py-3"
                        >
                            <!-- View mode -->
                            <template v-if="editingSubId !== sub.id">
                                <span class="flex-1 text-sm text-slate-800">{{ sub.name }}</span>
                                <button
                                    @click="startEdit(sub)"
                                    class="text-xs text-slate-500 hover:text-slate-800 px-2 py-1 rounded hover:bg-slate-100 transition-colors"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteSubCategory(sub)"
                                    class="text-xs text-rose-500 hover:text-rose-700 px-2 py-1 rounded hover:bg-rose-50 transition-colors"
                                >
                                    Delete
                                </button>
                            </template>
                            <!-- Edit mode -->
                            <template v-else>
                                <input
                                    v-model="editSubForm.name"
                                    type="text"
                                    class="flex-1 text-sm border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                                    @keydown.enter="updateSubCategory(sub)"
                                    @keydown.escape="cancelEdit"
                                />
                                <button
                                    @click="updateSubCategory(sub)"
                                    :disabled="editSubForm.processing"
                                    class="text-xs text-emerald-600 hover:text-emerald-700 px-2 py-1 rounded hover:bg-emerald-50 transition-colors font-medium"
                                >
                                    Save
                                </button>
                                <button @click="cancelEdit" class="text-xs text-slate-500 hover:text-slate-700 px-2 py-1 rounded hover:bg-slate-100 transition-colors">
                                    Cancel
                                </button>
                            </template>
                        </div>
                    </div>
                    <div v-else class="px-6 py-4 text-sm text-slate-400">
                        No sub-categories yet.
                    </div>

                    <!-- Add new sub-category -->
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                        <p class="text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Add Sub-category</p>
                        <form @submit.prevent="addSubCategory" class="flex items-center gap-2">
                            <input
                                v-model="subForm.name"
                                type="text"
                                placeholder="Sub-category name"
                                class="flex-1 text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                            />
                            <button
                                type="submit"
                                :disabled="subForm.processing || !subForm.name"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Add
                            </button>
                        </form>
                        <InputError :message="subForm.errors.name" class="mt-1.5" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
