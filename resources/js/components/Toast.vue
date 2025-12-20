<script setup lang="ts">
import { useToastState, removeToast, type Toast } from '@/composables/useToast'
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-vue-next'

import { usePage } from '@inertiajs/vue3'
import { watch, computed } from 'vue'
import { showToast } from '@/composables/useToast'

const state = useToastState()
const page = usePage()

const icons = {
    success: CheckCircle,
    error: AlertCircle,
    info: Info,
    warning: AlertTriangle,
}

// <CHANGE> Improved color schemes with better contrast and modern styling
const colors = {
    success: 'bg-white dark:bg-gray-950 border border-emerald-500',
    error: 'bg-white dark:bg-gray-950 border border-rose-500',
    info: 'bg-white dark:bg-gray-950 border border-blue-500',
    warning: 'bg-white dark:bg-gray-950 border border-amber-500',
}

const iconColors = {
    success: 'text-emerald-500',
    error: 'text-rose-500',
    info: 'text-blue-500',
    warning: 'text-amber-500',
}

const iconBgColors = {
    success: 'bg-emerald-50 dark:bg-emerald-500/10',
    error: 'bg-rose-50 dark:bg-rose-500/10',
    info: 'bg-blue-50 dark:bg-blue-500/10',
    warning: 'bg-amber-50 dark:bg-amber-500/10',
}

const buttonHoverColors = {
    success: 'hover:bg-emerald-100 dark:hover:bg-emerald-500/20',
    error: 'hover:bg-rose-100 dark:hover:bg-rose-500/20',
    info: 'hover:bg-blue-100 dark:hover:bg-blue-500/20',
    warning: 'hover:bg-amber-100 dark:hover:bg-amber-500/20',
}

function close(id: string) {
    removeToast(id)
}

watch(() => page.props.flash, (flash: any) => {
    let hasFlash = false
    if (flash?.success) {
        showToast({ message: flash.success, type: 'success' })
        hasFlash = true
    }
    if (flash?.error) {
        showToast({ message: flash.error, type: 'error' })
        hasFlash = true
    }
    if (flash?.warning) {
        showToast({ message: flash.warning, type: 'warning' })
        hasFlash = true
    }
    if (flash?.info) {
        showToast({ message: flash.info, type: 'info' })
        hasFlash = true
    }

    if (hasFlash) {
        page.props.flash = {
            success: null,
            error: null,
            warning: null,
            info: null
        }
    }
}, { deep: true, immediate: true })
</script>

<template>
    <div aria-live="assertive"
        class="fixed top-0 right-0 z-[9999] flex flex-col items-end gap-3 p-6 w-full md:max-w-md pointer-events-none">
        <TransitionGroup enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95">
            <div v-for="toast in state.toasts" :key="toast.id"
                class="pointer-events-auto w-full overflow-hidden rounded-xl shadow-lg ring-1 ring-gray-900/5 dark:ring-white/10 backdrop-blur-sm"
                :class="colors[toast.type || 'success']">
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <!-- <CHANGE> Added styled icon container with background -->
                        <div class="shrink-0 rounded-full p-2" :class="iconBgColors[toast.type || 'success']">
                            <component :is="icons[toast.type || 'success']" class="h-5 w-5"
                                :class="iconColors[toast.type || 'success']" aria-hidden="true" />
                        </div>
                        <!-- <CHANGE> Improved text styling and spacing -->
                        <div class="flex-1 min-w-0 pt-0.5">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-snug">
                                Thông báo
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ toast.message }}
                            </p>
                        </div>
                        <!-- <CHANGE> Enhanced close button with better hover states -->
                        <div class="shrink-0">
                            <button type="button" @click="close(toast.id)"
                                class="inline-flex rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
                                :class="buttonHoverColors[toast.type || 'success']">
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