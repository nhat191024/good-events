<template>
    <div v-if="state.loading" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative p-6 rounded-lg bg-white shadow-lg w-11/12 max-w-sm text-center">
            <div class="mb-4">
                <svg class="animate-spin h-8 w-8 mx-auto fill-white" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="black" stroke-width="4" stroke-opacity="0.25">
                    </circle>
                    <path d="M22 12a10 10 0 00-10-10" stroke="black" stroke-width="4"></path>
                </svg>
            </div>
            <h3 class="font-medium mb-1 text-black">{{ state.title }}</h3>
            <p class="text-sm mb-0 text-gray-500">{{ state.message }}</p>
            <button v-if="state.cancelable" class="text-sm underline" @click="onCancel" type="button">
                Huỷ
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useLoadingState, hideLoading } from '@/composables/useLoading'

const state = useLoadingState()

function onCancel() {
    hideLoading(false)
}
</script>
