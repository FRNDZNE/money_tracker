<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    categories: Array,
});

const incomeCategories = computed(() =>
    props.categories?.filter((c) => c.type === 'income') || [],
);
const expenseCategories = computed(() =>
    props.categories?.filter((c) => c.type === 'expense') || [],
);

const deleteCategory = (category) => {
    if (confirm(`Delete "${category.name}"? All sub-categories will also be deleted.`)) {
        router.delete(route('categories.destroy', category.id));
    }
};
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
                <Link
                    :href="route('categories.create')"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Category
                </Link>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-6">
            <!-- Income Categories -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-500" />
                    <h2 class="text-sm font-semibold text-slate-900">Income Categories</h2>
                    <span class="text-xs text-slate-400">({{ incomeCategories.length }})</span>
                </div>
                <div v-if="incomeCategories.length" class="divide-y divide-slate-100">
                    <div
                        v-for="category in incomeCategories"
                        :key="category.id"
                        class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 text-sm">{{ category.name }}</p>
                            <div v-if="category.sub_categories?.length" class="flex flex-wrap gap-1.5 mt-1.5">
                                <span
                                    v-for="sub in category.sub_categories"
                                    :key="sub.id"
                                    class="text-xs px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full"
                                >
                                    {{ sub.name }}
                                </span>
                            </div>
                            <p v-else class="text-xs text-slate-400 mt-1">No sub-categories</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="route('categories.edit', category.id)"
                                class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                            >
                                Edit
                            </Link>
                            <button
                                @click="deleteCategory(category)"
                                class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="px-6 py-8 text-center text-sm text-slate-400">
                    No income categories yet.
                    <Link :href="route('categories.create')" class="text-emerald-600 hover:underline ml-1">Add one →</Link>
                </div>
            </div>

            <!-- Expense Categories -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-rose-500" />
                    <h2 class="text-sm font-semibold text-slate-900">Expense Categories</h2>
                    <span class="text-xs text-slate-400">({{ expenseCategories.length }})</span>
                </div>
                <div v-if="expenseCategories.length" class="divide-y divide-slate-100">
                    <div
                        v-for="category in expenseCategories"
                        :key="category.id"
                        class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 text-sm">{{ category.name }}</p>
                            <div v-if="category.sub_categories?.length" class="flex flex-wrap gap-1.5 mt-1.5">
                                <span
                                    v-for="sub in category.sub_categories"
                                    :key="sub.id"
                                    class="text-xs px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full"
                                >
                                    {{ sub.name }}
                                </span>
                            </div>
                            <p v-else class="text-xs text-slate-400 mt-1">No sub-categories</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="route('categories.edit', category.id)"
                                class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                            >
                                Edit
                            </Link>
                            <button
                                @click="deleteCategory(category)"
                                class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="px-6 py-8 text-center text-sm text-slate-400">
                    No expense categories yet.
                    <Link :href="route('categories.create')" class="text-emerald-600 hover:underline ml-1">Add one →</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
