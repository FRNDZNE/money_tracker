<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    goals: Array,
});

// ── Create Modal ──────────────────────────────────────────
const showCreateModal = ref(false);

const createForm = useForm({
    name: '',
    target_amount: '',
    current_amount: '',
    target_date: '',
});

function openCreate() {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
}

function submitCreate() {
    createForm.post(route('savings-goals.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

// ── Edit Modal ────────────────────────────────────────────
const showEditModal  = ref(false);
const editingGoal    = ref(null);

const editForm = useForm({
    name: '',
    target_amount: '',
    current_amount: '',
    target_date: '',
});

function openEdit(goal) {
    editingGoal.value        = goal;
    editForm.name            = goal.name;
    editForm.target_amount   = goal.target_amount;
    editForm.current_amount  = goal.current_amount;
    editForm.target_date     = goal.target_date ?? '';
    editForm.clearErrors();
    showEditModal.value = true;
}

function submitEdit() {
    editForm.patch(route('savings-goals.update', editingGoal.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editingGoal.value   = null;
            editForm.reset();
        },
    });
}

// ── Top-Up Modal ──────────────────────────────────────────
const topUpGoal = ref(null);
const topUpForm = useForm({ amount: '' });

function openTopUp(goal) {
    topUpGoal.value  = goal;
    topUpForm.amount = '';
    topUpForm.clearErrors();
}

function submitTopUp() {
    topUpForm.post(route('savings-goals.top-up', topUpGoal.value.id), {
        onSuccess: () => {
            topUpGoal.value = null;
            topUpForm.reset();
        },
    });
}

// ── Delete ────────────────────────────────────────────────
function deleteGoal(id) {
    if (confirm('Delete this savings goal?')) {
        router.delete(route('savings-goals.destroy', id));
    }
}

// ── Helpers ───────────────────────────────────────────────
const formatIDR = (n) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);

function daysLeft(dateStr) {
    if (!dateStr) return null;
    const diff = Math.ceil((new Date(dateStr) - new Date()) / (1000 * 60 * 60 * 24));
    return diff;
}

function progressColor(pct) {
    if (pct >= 100) return 'bg-emerald-500';
    if (pct >= 60)  return 'bg-sky-500';
    if (pct >= 30)  return 'bg-amber-500';
    return 'bg-rose-400';
}
</script>

<template>
    <Head title="Savings Goals" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Savings Goals</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Track your financial targets</p>
                </div>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Goal
                </button>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <!-- Empty State -->
            <div v-if="goals.length === 0" class="text-center py-20">
                <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800 mb-1">No savings goals yet</h3>
                <p class="text-sm text-slate-500 mb-6">Set a target and start tracking your progress.</p>
                <button
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    Create First Goal
                </button>
            </div>

            <!-- Goals Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                <div
                    v-for="goal in goals"
                    :key="goal.id"
                    class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col gap-4"
                >
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900 leading-tight">{{ goal.name }}</h3>
                            <p v-if="goal.target_date" class="text-xs text-slate-500 mt-0.5">
                                <template v-if="daysLeft(goal.target_date) > 0">
                                    {{ daysLeft(goal.target_date) }} days left
                                </template>
                                <template v-else-if="daysLeft(goal.target_date) === 0">
                                    Due today
                                </template>
                                <template v-else>
                                    <span class="text-rose-500">Overdue by {{ Math.abs(daysLeft(goal.target_date)) }} days</span>
                                </template>
                            </p>
                        </div>
                        <!-- Completed Badge -->
                        <span
                            v-if="goal.progress >= 100"
                            class="shrink-0 text-xs font-semibold px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full"
                        >
                            Achieved! 🎉
                        </span>
                    </div>

                    <!-- Amounts -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Saved</span>
                            <span class="font-semibold text-slate-800">{{ formatIDR(goal.current_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Target</span>
                            <span class="font-medium text-slate-600">{{ formatIDR(goal.target_amount) }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div>
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Progress</span>
                            <span class="font-semibold">{{ goal.progress }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5">
                            <div
                                :class="['h-2.5 rounded-full transition-all duration-700', progressColor(goal.progress)]"
                                :style="{ width: goal.progress + '%' }"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                        <button
                            @click="openTopUp(goal)"
                            class="flex-1 text-xs font-medium px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg transition-colors"
                        >
                            + Top Up
                        </button>
                        <button
                            @click="openEdit(goal)"
                            class="p-1.5 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors"
                            title="Edit goal"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </button>
                        <button
                            @click="deleteGoal(goal.id)"
                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                            title="Delete goal"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Create Modal ──────────────────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showCreateModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click.self="showCreateModal = false"
                >
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                        <h3 class="text-base font-semibold text-slate-900 mb-1">New Savings Goal</h3>
                        <p class="text-sm text-slate-500 mb-5">Define a financial target to work towards</p>

                        <form @submit.prevent="submitCreate" class="space-y-4">
                            <!-- Name -->
                            <div>
                                <InputLabel for="create-name" value="Goal Name" class="mb-1.5" />
                                <TextInput
                                    id="create-name"
                                    v-model="createForm.name"
                                    type="text"
                                    placeholder="e.g. New Laptop, Emergency Fund"
                                    class="w-full"
                                    autofocus
                                />
                                <InputError :message="createForm.errors.name" class="mt-1.5" />
                            </div>

                            <!-- Target Amount -->
                            <div>
                                <InputLabel for="create-target" value="Target Amount (IDR)" class="mb-1.5" />
                                <TextInput
                                    id="create-target"
                                    v-model="createForm.target_amount"
                                    type="number"
                                    min="1"
                                    step="1000"
                                    placeholder="e.g. 15000000"
                                    class="w-full"
                                />
                                <InputError :message="createForm.errors.target_amount" class="mt-1.5" />
                            </div>

                            <!-- Current Amount -->
                            <div>
                                <InputLabel for="create-current" value="Already Saved (IDR)" class="mb-1.5" />
                                <TextInput
                                    id="create-current"
                                    v-model="createForm.current_amount"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    placeholder="Leave empty to start from 0"
                                    class="w-full"
                                />
                                <p class="mt-1 text-xs text-slate-400">Optional — leave blank if starting from zero</p>
                                <InputError :message="createForm.errors.current_amount" class="mt-1" />
                            </div>

                            <!-- Target Date -->
                            <div>
                                <InputLabel for="create-date" value="Target Date (optional)" class="mb-1.5" />
                                <TextInput
                                    id="create-date"
                                    v-model="createForm.target_date"
                                    type="date"
                                    class="w-full"
                                />
                                <InputError :message="createForm.errors.target_date" class="mt-1.5" />
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3 pt-2">
                                <button
                                    type="submit"
                                    :disabled="createForm.processing"
                                    class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                                >
                                    {{ createForm.processing ? 'Creating...' : 'Create Goal' }}
                                </button>
                                <button
                                    type="button"
                                    @click="showCreateModal = false"
                                    class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Edit Modal ────────────────────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showEditModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click.self="showEditModal = false"
                >
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                        <h3 class="text-base font-semibold text-slate-900 mb-1">Edit Savings Goal</h3>
                        <p class="text-sm text-slate-500 mb-5">Update your financial target</p>

                        <form @submit.prevent="submitEdit" class="space-y-4">
                            <!-- Name -->
                            <div>
                                <InputLabel for="edit-name" value="Goal Name" class="mb-1.5" />
                                <TextInput
                                    id="edit-name"
                                    v-model="editForm.name"
                                    type="text"
                                    class="w-full"
                                    autofocus
                                />
                                <InputError :message="editForm.errors.name" class="mt-1.5" />
                            </div>

                            <!-- Target Amount -->
                            <div>
                                <InputLabel for="edit-target" value="Target Amount (IDR)" class="mb-1.5" />
                                <TextInput
                                    id="edit-target"
                                    v-model="editForm.target_amount"
                                    type="number"
                                    min="1"
                                    step="1000"
                                    class="w-full"
                                />
                                <InputError :message="editForm.errors.target_amount" class="mt-1.5" />
                            </div>

                            <!-- Current Amount -->
                            <div>
                                <InputLabel for="edit-current" value="Already Saved (IDR)" class="mb-1.5" />
                                <TextInput
                                    id="edit-current"
                                    v-model="editForm.current_amount"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    placeholder="Leave empty to start from 0"
                                    class="w-full"
                                />
                                <p class="mt-1 text-xs text-slate-400">Optional — leave blank if starting from zero</p>
                                <InputError :message="editForm.errors.current_amount" class="mt-1" />
                            </div>

                            <!-- Target Date -->
                            <div>
                                <InputLabel for="edit-date" value="Target Date (optional)" class="mb-1.5" />
                                <TextInput
                                    id="edit-date"
                                    v-model="editForm.target_date"
                                    type="date"
                                    class="w-full"
                                />
                                <InputError :message="editForm.errors.target_date" class="mt-1.5" />
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3 pt-2">
                                <button
                                    type="submit"
                                    :disabled="editForm.processing"
                                    class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                                >
                                    {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                                </button>
                                <button
                                    type="button"
                                    @click="showEditModal = false"
                                    class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Top-Up Modal ──────────────────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="topUpGoal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click.self="topUpGoal = null"
                >
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                        <h3 class="text-base font-semibold text-slate-900 mb-1">Top Up Savings</h3>
                        <p class="text-sm text-slate-500 mb-5">Add savings to <span class="font-medium text-slate-700">{{ topUpGoal.name }}</span></p>

                        <form @submit.prevent="submitTopUp" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Amount (IDR)</label>
                                <input
                                    v-model="topUpForm.amount"
                                    type="number"
                                    min="0.01"
                                    step="1000"
                                    placeholder="e.g. 500000"
                                    class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    autofocus
                                />
                                <p v-if="topUpForm.errors.amount" class="mt-1 text-xs text-rose-600">{{ topUpForm.errors.amount }}</p>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    type="submit"
                                    :disabled="topUpForm.processing"
                                    class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors"
                                >
                                    {{ topUpForm.processing ? 'Adding...' : 'Add Savings' }}
                                </button>
                                <button
                                    type="button"
                                    @click="topUpGoal = null"
                                    class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
