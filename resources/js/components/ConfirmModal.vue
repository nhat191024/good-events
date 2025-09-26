<script setup lang="ts">
import { toRef } from 'vue'
import {
    DialogRoot,
    DialogPortal,
    DialogOverlay,
    DialogContent,
    DialogTitle,
    DialogDescription,
    DialogClose,
} from 'reka-ui'
import Icon from '@/components/Icon.vue'
import { useConfirmState } from '@/composables/useConfirm'

const state = useConfirmState()
// DialogRoot controlled bằng v-model:open
const openRef = toRef(state, 'open')

function onConfirm() {
    state.resolve?.(true)
    // state.open will be closed by resolver in composable
}

function onCancel() {
    state.resolve?.(false)
    state.open = false
}
</script>

<template>
    <DialogRoot v-model:open="openRef">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-40 bg-white/20 backdrop-blur-[3px]" />
            <DialogContent
                class="fixed top-[50%] left-[50%] max-w-[480px] w-[90vw] shadow-lg -translate-x-1/2 -translate-y-1/2 rounded-lg bg-white p-6 z-50">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <DialogTitle class="text-black text-lg font-semibold">{{ state.title }}</DialogTitle>
                        <DialogDescription class="text-sm mt-2 text-black">
                            <div v-html="state.message"></div>
                        </DialogDescription>
                    </div>

                    <DialogClose as-child>
                        <button aria-label="Close" class="h-8 w-8 rounded-full hover:bg-gray-100">
                            <Icon name="x" />
                        </button>
                    </DialogClose>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="onCancel" class="rounded px-3 py-1 border text-black">
                        {{ state.cancelText }}
                    </button>
                    <button @click="onConfirm" class="rounded px-3 py-1 bg-primary-600 text-white font-bold">
                        {{ state.okText }}
                    </button>
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
