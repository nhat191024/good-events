<script setup lang="ts">
const props = defineProps<{ user: { email: string; phone: string } }>()
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hidden">
        <h3 class="font-semibold text-rose-600 mb-3">Liên hệ</h3>

        <!-- Primary action button -->
        <button
            class="w-full h-11 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-medium flex items-center justify-center gap-2 transition-colors mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            Nhắn tin ngay
        </button>

        <!-- Secondary actions -->
        <div class="grid grid-cols-1 gap-2 mb-4">
            <!-- <a :href="`tel:${user.phone}`"
                class="h-10 rounded-lg border border-gray-300 hover:bg-gray-50 flex items-center justify-center gap-2 text-sm font-medium text-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                Gọi
            </a> -->
            <a :href="`mailto:${user.email}`"
                class="h-10 rounded-lg border border-gray-300 hover:bg-gray-50 flex items-center justify-center gap-2 text-sm font-medium text-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Email
            </a>
        </div>

        <!-- Additional info -->
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Thời gian phản hồi:</span>
                <span class="font-medium text-gray-900">Trong 1 giờ</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Ngôn ngữ:</span>
                <span class="font-medium text-gray-900">Tiếng Việt, English</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Múi giờ:</span>
                <span class="font-medium text-gray-900">GMT+7</span>
            </div>
        </div>
    </div>
</template>
