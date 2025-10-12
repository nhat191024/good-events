<script setup lang="ts">
    import { computed } from 'vue'
    import { usePage } from '@inertiajs/vue3'
    import type { Message } from '../types'

    const props = defineProps<{ message: Message }>()

    const page = usePage()

    const currentUserId = computed(() => {
        return (page.props.auth as any)?.user?.id || null
    })

    const isMe = computed(() => props.message.user_id === currentUserId.value)

    const timeText = computed(() => {
        const date = new Date(props.message.created_at)
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
    })
</script>

<template>
    <div :class="['flex gap-2', isMe ? 'justify-end' : 'justify-start']">
        <div :class="['max-w-[75%] space-y-1', isMe ? 'items-end' : '']">
            <div v-if="!isMe" class="text-xs text-gray-600 px-1 mb-1">
                {{ message.user.name }}
            </div>

            <div :class="[
                'px-4 py-2 rounded-2xl text-sm leading-relaxed shadow-sm',
                isMe ? 'bg-red-500 text-white rounded-br-md' : 'bg-gray-200 text-gray-900 rounded-bl-md',
            ]">
                {{ message.body }}
            </div>

            <div :class="['flex items-center gap-1 text-xs text-gray-500 px-1', isMe ? 'justify-end' : '']">
                <span>{{ timeText }}</span>
            </div>
        </div>
    </div>
</template>
