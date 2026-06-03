<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    budgets: Array,
    categories: Array,
    filters: Object,
});

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const selectedMonth = ref(props.filters.month);
const selectedYear  = ref(props.filters.year);

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);

function applyFilter() {
    router.get(route('budgets.index'), {
        month: selectedMonth.value,
        year: selectedYear.value,
    }, { preserveState: true, replace: true });
}

// Add budget form
const addForm = useForm({
    category_id: '',
    month: props.filters.month,
    year: props.filters.year,
    amount: '',
});

function submitAdd() {
    addForm.post(route('budgets.store'), {
        onSuccess: () => {
            addForm.reset('category_id', 'amount');
        },
    });
}

// Edit budget inline
const editingId  = ref(null);
const editAmount = ref('');

function startEdit(budget) {
    editingId.value  = budget.id;
    editAmount.value = budget.amount;
}

function cancelEdit() {
    editingId.value  = null;
    editAmount.value = '';
}

function saveEdit(budget) {
    router.patch(route('budgets.update', budget.id), { amount: editAmount.value }, {
        onSuccess: () => cancelEdit(),
    });
}

function deleteBudget(id) {
    if (confirm('Remove this budget?')) {
        router.delete(route('budgets.destroy', id));
    }
}

function progressColor(spent, amount) {
    if (amount === 0) return 'bg-slate-300';
    const pct = spent / amount;
    if (pct >= 1) return 'bg-rose-500';
    if (pct >= 0.8) return 'bg-amber-500';
    return 'bg-emerald-500';
}

function progressWidth(spent, amount) {
    if (amount === 0) return '0%';
    return Math.min(100, Math.round((spent / amount) * 100)) + '%';
}

const formatIDR = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);
</script>

<template>
    <Head title="Budgets" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Budget Management</h1>
                <p class="text-sm text-slate-500 mt-0.5">Set monthly spending limits per category</p>
            </div>
        </template>

        <div class="p-6 lg:p-8 space-y-6">

            <!-- Month/Year Filter -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-slate-600">Month</label>
                        <select
                            v-model="selectedMonth"
                            class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option v-for="(name, i) in months" :key="i + 1" :value="i + 1">{{ name }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-slate-600">Year</label>
                        <select
                            v-model="selectedYear"
                            class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                    <button
                        @click="applyFilter"
                        class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                    >
                        Apply
                    </button>
                </div>
            </div>

            <!-- Existing Budgets -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800">
                        Budgets for {{ months[filters.month - 1] }} {{ filters.year }}
                    </h2>
                    <span class="text-xs text-slate-500">{{ budgets.length }} budget(s)</span>
                </div>

                <div v-if="budgets.length === 0" class="px-6 py-12 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-slate-500 text-sm">No budgets set for this month. Add one below.</p>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li v-for="budget in budgets" :key="budget.id" class="px-6 py-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-slate-800">{{ budget.category_name }}</span>
                            <div class="flex items-center gap-2">
                                <!-- Inline Edit -->
                                <template v-if="editingId === budget.id">
                                    <input
                                        v-model="editAmount"
                                        type="number"
                                        min="0"
                                        class="w-36 text-sm border border-slate-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    />
                                    <button @click="saveEdit(budget)" class="text-xs px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">Save</button>
                                    <button @click="cancelEdit" class="text-xs px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors">Cancel</button>
                                </template>
                                <template v-else>
                                    <span class="text-sm text-slate-600">{{ formatIDR(budget.amount) }}</span>
                                    <button @click="startEdit(budget)" class="p-1 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button @click="deleteBudget(budget.id)" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-100 rounded-full h-2 mb-1.5">
                            <div
                                :class="['h-2 rounded-full transition-all duration-500', progressColor(budget.spent, budget.amount)]"
                                :style="{ width: progressWidth(budget.spent, budget.amount) }"
                            />
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Spent: <span class="font-medium text-slate-700">{{ formatIDR(budget.spent) }}</span></span>
                            <span>Remaining: <span :class="['font-medium', budget.spent > budget.amount ? 'text-rose-600' : 'text-emerald-600']">
                                {{ formatIDR(Math.max(0, budget.amount - budget.spent)) }}
                            </span></span>
                            <span>{{ budget.amount > 0 ? Math.round((budget.spent / budget.amount) * 100) : 0 }}%</span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Add Budget Form -->
            <div v-if="categories.length > 0" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-800 mb-4">Add Budget</h2>
                <form @submit.prevent="submitAdd" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Category</label>
                        <select
                            v-model="addForm.category_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="" disabled>Select category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <p v-if="addForm.errors.category_id" class="mt-1 text-xs text-rose-600">{{ addForm.errors.category_id }}</p>
                    </div>
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Amount (IDR)</label>
                        <input
                            v-model="addForm.amount"
                            type="number"
                            min="0"
                            step="1000"
                            placeholder="e.g. 1500000"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                        <p v-if="addForm.errors.amount" class="mt-1 text-xs text-rose-600">{{ addForm.errors.amount }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="addForm.processing"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                    >
                        {{ addForm.processing ? 'Saving...' : 'Add Budget' }}
                    </button>
                </form>
            </div>

            <div v-else-if="budgets.length > 0" class="text-center py-4 text-sm text-slate-500">
                All expense categories have budgets set for this month.
            </div>

        </div>
    </AuthenticatedLayout>
</template>
