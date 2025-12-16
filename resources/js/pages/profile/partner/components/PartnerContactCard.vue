<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps<{ contact: { phone: string | null; email: string | null; response_time: string; languages: string[]; timezone: string } }>()
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
        <!-- Red title matching design -->
        <h3 class="font-semibold mb-0 text-rose-600">Liên hệ</h3>
        <div class="space-y-2">
            <div class="pt-3 border-t text-sm text-gray-600 space-y-2">
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
