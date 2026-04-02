<script setup>
const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'تأكيد العملية' },
    message: { type: String, default: 'هل أنت متأكد من هذا الإجراء؟' },
    confirmText: { type: String, default: 'تأكيد' },
    cancelText: { type: String, default: 'إلغاء' },
    danger: { type: Boolean, default: true },
});

const emit = defineEmits(['confirm', 'cancel']);

function onConfirm() {
    emit('confirm');
}
function onCancel() {
    emit('cancel');
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="direction:rtl">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="onCancel"></div>

                <!-- Modal Content -->
                <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl transform transition-all"
                     :class="show ? 'scale-100 opacity-100' : 'scale-95 opacity-0'">
                    <!-- Icon -->
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full"
                         :class="danger ? 'bg-red-100' : 'bg-blue-100'">
                        <svg v-if="danger" class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <svg v-else class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                    </div>

                    <!-- Title -->
                    <h3 class="mb-2 text-lg font-bold text-gray-900">{{ title }}</h3>

                    <!-- Message -->
                    <p class="mb-6 text-sm text-gray-500 leading-relaxed">{{ message }}</p>

                    <!-- Buttons -->
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click.stop="onCancel"
                            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 transition cursor-pointer">
                            {{ cancelText }}
                        </button>
                        <button type="button" @click.stop="onConfirm"
                            class="rounded-lg px-5 py-2.5 text-sm font-medium text-white transition cursor-pointer"
                            :class="danger ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'">
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
