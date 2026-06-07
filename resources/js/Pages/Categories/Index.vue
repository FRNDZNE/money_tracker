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
    categories: Array,
});

// ── Category modal ────────────────────────────────────────
const showModal = ref(false);
const editingCategoryId = ref(null);

// Always reflects fresh props after server round-trips
const editingCategory = computed(() =>
    props.categories?.find((c) => c.id === editingCategoryId.value) ?? null,
);

const form = useForm({ name: '', type: 'expense' });

const openCreate = () => {
    editingCategoryId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (category) => {
    editingCategoryId.value = category.id;
    form.name = category.name;
    form.type = category.type;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCategoryId.value = null;
};

const submit = () => {
    if (editingCategoryId.value) {
        form.put(route('categories.update', editingCategoryId.value), { onSuccess: closeModal });
    } else {
        form.post(route('categories.store'), { onSuccess: closeModal });
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

const deleteCategory = async (category) => {
    const ok = await confirmAction({
        title: `Delete "${category.name}"?`,
        message: 'All sub-categories will also be permanently deleted.',
        type: 'danger',
        confirmText: 'Delete',
    });
    if (ok) router.delete(route('categories.destroy', category.id));
};

// ── Sub-category management (inside edit modal) ───────────
const subAddForm = useForm({ name: '' });
const editingSubId = ref(null);
const subEditForm = useForm({ name: '' });

const addSub = () => {
    subAddForm.post(route('categories.sub-categories.store', editingCategoryId.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => subAddForm.reset(),
    });
};

const startEditSub = (sub) => {
    editingSubId.value = sub.id;
    subEditForm.name = sub.name;
    subEditForm.clearErrors();
};

const cancelEditSub = () => {
    editingSubId.value = null;
    subEditForm.reset();
};

const saveSub = (sub) => {
    subEditForm.put(
        route('categories.sub-categories.update', [editingCategoryId.value, sub.id]),
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                editingSubId.value = null;
                subEditForm.reset();
            },
        },
    );
};

const deleteSub = async (sub) => {
    const ok = await confirmAction({
        title: `Delete "${sub.name}"?`,
        message: 'This sub-category will be removed from all existing transactions.',
        type: 'danger',
        confirmText: 'Delete',
    });
    if (ok) {
        router.delete(route('categories.sub-categories.destroy', [editingCategoryId.value, sub.id]), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

// ── Helpers ───────────────────────────────────────────────
const incomeCategories = computed(() => props.categories?.filter((c) => c.type === 'income') || []);
const expenseCategories = computed(() => props.categories?.filter((c) => c.type === 'expense') || []);
</script>

<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Categories</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Organize your income and expense categories</p>
                </div>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Category
                </button>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-6">
            <!-- Income -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-500" />
                    <h2 class="text-sm font-semibold text-slate-900">Income Categories</h2>
                    <span class="text-xs text-slate-400">({{ incomeCategories.length }})</span>
                </div>
                <div v-if="incomeCategories.length" class="divide-y divide-slate-100">
                    <div v-for="cat in incomeCategories" :key="cat.id" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 text-sm">{{ cat.name }}</p>
                            <div v-if="cat.sub_categories?.length" class="flex flex-wrap gap-1.5 mt-1.5">
                                <span v-for="sub in cat.sub_categories" :key="sub.id" class="text-xs px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">{{ sub.name }}</span>
                            </div>
                            <p v-else class="text-xs text-slate-400 mt-0.5">No sub-categories</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="openEdit(cat)" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Edit</button>
                            <button @click="deleteCategory(cat)" class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">Delete</button>
                        </div>
                    </div>
                </div>
                <div v-else class="px-6 py-8 text-center text-sm text-slate-400">
                    No income categories yet. <button @click="openCreate" class="text-emerald-600 hover:underline">Add one →</button>
                </div>
            </div>

            <!-- Expense -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-rose-500" />
                    <h2 class="text-sm font-semibold text-slate-900">Expense Categories</h2>
                    <span class="text-xs text-slate-400">({{ expenseCategories.length }})</span>
                </div>
                <div v-if="expenseCategories.length" class="divide-y divide-slate-100">
                    <div v-for="cat in expenseCategories" :key="cat.id" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 text-sm">{{ cat.name }}</p>
                            <div v-if="cat.sub_categories?.length" class="flex flex-wrap gap-1.5 mt-1.5">
                                <span v-for="sub in cat.sub_categories" :key="sub.id" class="text-xs px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">{{ sub.name }}</span>
                            </div>
                            <p v-else class="text-xs text-slate-400 mt-0.5">No sub-categories</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button @click="openEdit(cat)" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Edit</button>
                            <button @click="deleteCategory(cat)" class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">Delete</button>
                        </div>
                    </div>
                </div>
                <div v-else class="px-6 py-8 text-center text-sm text-slate-400">
                    No expense categories yet. <button @click="openCreate" class="text-emerald-600 hover:underline">Add one →</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Category Modal -->
    <Modal
        :show="showModal"
        :title="editingCategoryId ? 'Edit Category' : 'Add Category'"
        max-width="lg"
        @close="closeModal"
    >
        <!-- Category form -->
        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="cat-name" value="Category Name" class="mb-1.5" />
                <TextInput id="cat-name" v-model="form.name" type="text" placeholder="e.g. Food, Salary, Transport" class="w-full" autofocus />
                <InputError :message="form.errors.name" class="mt-1.5" />
            </div>

            <div>
                <InputLabel value="Type" class="mb-1.5" />
                <div class="grid grid-cols-2 gap-3">
                    <label
                        v-for="opt in [
                            { value: 'expense', label: 'Expense', icon: '📤', desc: 'Money going out' },
                            { value: 'income', label: 'Income', icon: '📥', desc: 'Money coming in' },
                        ]"
                        :key="opt.value"
                        :class="[
                            'flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all',
                            form.type === opt.value
                                ? (opt.value === 'expense' ? 'border-rose-500 bg-rose-50' : 'border-blue-500 bg-blue-50')
                                : 'border-slate-200 hover:border-slate-300',
                        ]"
                    >
                        <input type="radio" v-model="form.type" :value="opt.value" class="sr-only" />
                        <span class="text-xl">{{ opt.icon }}</span>
                        <div>
                            <p :class="['text-sm font-semibold', form.type === opt.value ? (opt.value === 'expense' ? 'text-rose-700' : 'text-blue-700') : 'text-slate-700']">{{ opt.label }}</p>
                            <p class="text-xs text-slate-400">{{ opt.desc }}</p>
                        </div>
                    </label>
                </div>
                <InputError :message="form.errors.type" class="mt-1.5" />
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                    {{ form.processing ? 'Saving...' : (editingCategoryId ? 'Save Changes' : 'Create Category') }}
                </button>
                <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            </div>
        </form>

        <!-- Sub-categories (edit mode only) -->
        <div v-if="editingCategoryId && editingCategory" class="mt-6 border-t border-slate-100 pt-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Sub-categories</p>

            <div v-if="editingCategory.sub_categories?.length" class="space-y-1 mb-3">
                <div v-for="sub in editingCategory.sub_categories" :key="sub.id" class="flex items-center gap-2">
                    <template v-if="editingSubId !== sub.id">
                        <span class="flex-1 text-sm text-slate-700 px-3 py-1.5 bg-slate-50 rounded-lg">{{ sub.name }}</span>
                        <button @click="startEditSub(sub)" class="text-xs text-slate-500 hover:text-slate-800 px-2 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">Edit</button>
                        <button @click="deleteSub(sub)" class="text-xs text-rose-500 hover:text-rose-700 px-2 py-1.5 rounded-lg hover:bg-rose-50 transition-colors">Delete</button>
                    </template>
                    <template v-else>
                        <input
                            v-model="subEditForm.name"
                            type="text"
                            class="flex-1 text-sm border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            @keydown.enter.prevent="saveSub(sub)"
                            @keydown.escape="cancelEditSub"
                            autofocus
                        />
                        <button @click="saveSub(sub)" :disabled="subEditForm.processing" class="text-xs text-emerald-600 hover:text-emerald-700 px-2 py-1.5 rounded-lg hover:bg-emerald-50 transition-colors font-medium">Save</button>
                        <button @click="cancelEditSub" class="text-xs text-slate-500 px-2 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">Cancel</button>
                    </template>
                </div>
            </div>
            <p v-else class="text-sm text-slate-400 mb-3">No sub-categories yet.</p>

            <!-- Add new sub -->
            <form @submit.prevent="addSub" class="flex items-center gap-2">
                <input
                    v-model="subAddForm.name"
                    type="text"
                    placeholder="New sub-category name"
                    class="flex-1 text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white"
                />
                <button type="submit" :disabled="subAddForm.processing || !subAddForm.name" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 disabled:opacity-50 text-white text-xs font-medium rounded-lg transition-colors">
                    Add
                </button>
            </form>
            <InputError :message="subAddForm.errors.name" class="mt-1.5" />
        </div>
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
