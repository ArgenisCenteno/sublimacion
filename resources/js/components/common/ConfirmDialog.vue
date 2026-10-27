<template>
    <Modal @close="$emit('cancel')">
        <template #body>
            <div
                class="w-full max-w-sm rounded-[24px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ title }}
                </h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ message }}
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="$emit('cancel')"
                    >
                        {{ cancelLabel }}
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700"
                        @click="$emit('confirm')"
                    >
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/ui/Modal.vue';

withDefaults(
    defineProps<{
        title: string;
        message: string;
        confirmLabel?: string;
        cancelLabel?: string;
    }>(),
    {
        confirmLabel: 'Eliminar',
        cancelLabel: 'Cancelar',
    },
);

defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>
