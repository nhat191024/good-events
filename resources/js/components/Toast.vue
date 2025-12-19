<script setup lang="ts">
import { useToastState, removeToast, type Toast } from '@/composables/useToast'
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-vue-next'

import { usePage } from '@inertiajs/vue3'
import { watch, computed } from 'vue'
import { showToast } from '@/composables/useToast'

const state = useToastState()
const page = usePage()

watch(() => page.props.flash, (flash: any) => {
    if (flash?.success) {
        showToast({ message: flash.success, type: 'success' })
    }
    if (flash?.error) {
        showToast({ message: flash.error, type: 'error' })
    }
    if (flash?.warning) {
        showToast({ message: flash.warning, type: 'warning' })
    }
    if (flash?.info) {
        showToast({ message: flash.info, type: 'info' })
    }
}, { deep: true, immediate: true })

const icons = {
    success: CheckCircle,
    error: AlertCircle,
    info: Info,
    warning: AlertTriangle,
}

const colors = {
    success: 'bg-green-50 text-green-800 border-green-200 dark:bg-green-900/10 dark:text-green-400 dark:border-green-800',
    error: 'bg-red-50 text-red-800 border-red-200 dark:bg-red-900/10 dark:text-red-400 dark:border-red-800',
    info: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/10 dark:text-blue-400 dark:border-blue-800',
    warning: 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/10 dark:text-yellow-400 dark:border-yellow-800',
}

function close(id: string) {
    removeToast(id)
}
</script>

<template>
    <div aria-live="assertive"
        class="fixed top-0 right-0 z-[100] flex flex-col items-end gap-2 p-4 w-full md:max-w-sm pointer-events-none">
        <TransitionGroup enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-for="toast in state.toasts" :key="toast.id"
                class="pointer-events-auto w-full overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800 dark:ring-gray-700">
                <div class="p-4" :class="colors[toast.type || 'success']">
                    <div class="flex items-start">
                        <div class="shrink-0">
                            <component :is="icons[toast.type || 'success']" class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p v-if="toast.title" class="text-sm font-medium">
                                {{ toast.title }}
                            </p>
                            <p class="text-sm" :class="{ 'mt-1': toast.title }">
                                {{ toast.message }}
                            </p>
                        </div>
                        <div class="ml-4 flex shrink-0">
                            <button type="button" @click="close(toast.id)"
                                class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 opacity-60 hover:opacity-100 transition-opacity"
                                :class="colors[toast.type || 'success']">
                                <span class="sr-only">Close</span>
                                <X class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>
