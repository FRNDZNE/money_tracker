<script setup>
/**
 * ConfirmDialog — a beautiful, animated confirm modal that replaces the
 * native browser confirm() dialog. Works via a global composable.
 *
 * Usage: import { useConfirm } from '@/Composables/useConfirm'
 *        const confirm = useConfirm()
 *        await confirm({ title: '...', message: '...' })
 */
import { ref, watch } from 'vue';

const props = defineProps({
    show:        { type: Boolean, default: false },
    title:       { type: String,  default: 'Are you sure?' },
    message:     { type: String,  default: '' },
    confirmText: { type: String,  default: 'Delete' },
    cancelText:  { type: String,  default: 'Cancel' },
    type:        { type: String,  default: 'danger' }, // 'danger' | 'warning' | 'info'
});

const emit = defineEmits(['confirm', 'cancel']);

const visible = ref(false);
const animateIn = ref(false);

watch(() => props.show, (val) => {
    if (val) {
        visible.value = true;
        requestAnimationFrame(() => { animateIn.value = true; });
    } else {
        animateIn.value = false;
        setTimeout(() => { visible.value = false; }, 200);
    }
});

const iconColors = {
    danger:  { bg: 'bg-rose-100',   icon: 'text-rose-500',   btn: 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500' },
    warning: { bg: 'bg-amber-100',  icon: 'text-amber-500',  btn: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500' },
    info:    { bg: 'bg-blue-100',   icon: 'text-blue-500',   btn: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' },
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                style="background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px);"
                @click.self="emit('cancel')"
                role="dialog"
                aria-modal="true"
            >
                <Transition
                    enter-active-class="transition-all ease-out duration-200"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all ease-in duration-150"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div
                        v-if="visible"
                        class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative"
                    >
                        <!-- Icon -->
                        <div :class="['w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4', iconColors[type]?.bg ?? iconColors.danger.bg]">
                            <!-- Danger / Delete icon -->
                            <svg v-if="type === 'danger'" :class="['w-6 h-6', iconColors.danger.icon]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <!-- Warning icon -->
                            <svg v-else-if="type === 'warning'" :class="['w-6 h-6', iconColors.warning.icon]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <!-- Info icon -->
                            <svg v-else :class="['w-6 h-6', iconColors.info.icon]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        </div>

                        <!-- Title -->
                        <h3 class="text-base font-semibold text-slate-900 text-center mb-2">{{ title }}</h3>

                        <!-- Message -->
                        <p v-if="message" class="text-sm text-slate-500 text-center mb-6 leading-relaxed">{{ message }}</p>
                        <div v-else class="mb-6" />

                        <!-- Actions -->
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="emit('cancel')"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300"
                            >
                                {{ cancelText }}
                            </button>
                            <button
                                type="button"
                                @click="emit('confirm')"
                                :class="['flex-1 px-4 py-2.5 text-sm font-medium text-white rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2', iconColors[type]?.btn ?? iconColors.danger.btn]"
                            >
                                {{ confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
