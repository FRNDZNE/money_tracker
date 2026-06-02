<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    transfers: Object, // paginated
});

const formatCurrency = (amount) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount || 0);

const formatDate = (date) =>
    new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });

const deleteTransfer = (id) => {
    if (confirm('Delete this transfer?')) {
        router.delete(route('transfers.destroy', id));
    }
};
</script>

<template>
    <Head title="Transfers" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Transfers</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Money movements between accounts</p>
                </div>
                <Link
                    :href="route('transfers.create')"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Transfer
                </Link>
            </div>
        </template>

        <div class="p-6 lg:p-8">
            <div v-if="transfers?.data?.length" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">From</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">To</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Note</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="transfer in transfers.data"
                            :key="transfer.id"
                            class="hover:bg-slate-50 transition-colors"
                        >
                            <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap">{{ formatDate(transfer.transfer_date) }}</td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-rose-100 rounded-full flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-slate-800">{{ transfer.from_account?.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-emerald-100 rounded-full flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l4-4m0 0l4 4m-4-4V3" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-slate-800">{{ transfer.to_account?.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-right font-semibold text-slate-900">{{ formatCurrency(transfer.amount) }}</td>
                            <td class="px-6 py-3.5 text-slate-500 max-w-40 truncate">{{ transfer.note || '—' }}</td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('transfers.edit', transfer.id)"
                                        class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteTransfer(transfer.id)"
                                        class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="transfers.last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Showing {{ transfers.from }}–{{ transfers.to }} of {{ transfers.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in transfers.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                link.active
                                    ? 'bg-emerald-600 text-white'
                                    : link.url
                                        ? 'text-slate-600 hover:bg-slate-100'
                                        : 'text-slate-300 cursor-not-allowed',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm p-16 text-center">
                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900 mb-1">No transfers yet</h3>
                <p class="text-sm text-slate-500 mb-4">Record a transfer to move money between your accounts.</p>
                <Link
                    :href="route('transfers.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    New Transfer
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
