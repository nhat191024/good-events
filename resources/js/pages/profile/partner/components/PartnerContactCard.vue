<script setup lang="ts">
const props = defineProps<{ contact: { phone: string | null; email: string | null; response_time: string; languages: string[]; timezone: string } }>()
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
        <!-- Red title matching design -->
        <h3 class="font-semibold mb-3 text-rose-600">Liên hệ</h3>
        <div class="space-y-2">
            <button
                class="w-full py-2.5 rounded-lg bg-rose-600 text-white font-medium hover:bg-rose-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Nhắn tin ngay
            </button>
            <div class="grid grid-cols-1 gap-2">
                <!-- <a :href="contact.phone ? `tel:${contact.phone}` : '#'"
                    class="flex items-center justify-center gap-1 py-2 rounded-lg ring-1 ring-gray-200 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Gọi
                </a> -->
                <a :href="contact.email ? `mailto:${contact.email}` : '#'"
                    class="flex items-center justify-center gap-1 py-2 rounded-lg ring-1 ring-gray-200 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email
                </a>
            </div>
            <div class="mt-3 pt-3 border-t text-sm text-gray-600 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Thời gian phản hồi:</span>
                    <span class="font-medium text-gray-900">{{ contact.response_time }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Ngôn ngữ:</span>
                    <span class="font-medium text-gray-900">{{ contact.languages.join(', ') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Múi giờ:</span>
                    <span class="font-medium text-gray-900">{{ contact.timezone }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
