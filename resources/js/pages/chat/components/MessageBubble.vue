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

    const isImageMessage = computed(() => props.message.type === 'image')

    const timeText = computed(() => {
        const date = new Date(props.message.created_at)
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
    })

    const mapsUrl = computed(() => {
        if (!props.message.location) {
            return null
        }

        return `https://www.google.com/maps?q=${props.message.location.latitude},${props.message.location.longitude}`
    })
</script>

<template>
    <div :class="['flex gap-2', isMe ? 'justify-end' : 'justify-start']">
        <div :class="['max-w-[75%] space-y-1', isMe ? 'items-end' : '']">
            <div v-if="!isMe" class="text-xs text-gray-600 px-1 mb-1">
                {{ message.user.name }}
            </div>

            <div :class="[
                'text-sm leading-relaxed',
                isImageMessage
                    ? 'space-y-2'
                    : 'rounded-2xl px-4 py-2 shadow-sm',
                !isImageMessage && (isMe ? 'bg-red-500 text-white rounded-br-md' : 'bg-gray-200 text-gray-900 rounded-bl-md'),
            ]">
                <template v-if="message.type === 'image'">
                    <div
                        :class="[
                            'inline-grid gap-2 align-top',
                            message.attachments.length > 1 ? 'grid-cols-2' : 'grid-cols-1',
                        ]"
                    >
                        <a
                            v-for="attachment in message.attachments"
                            :key="attachment.id"
                            :href="attachment.url"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <img
                                :alt="attachment.name || 'Ảnh chat'"
                                :src="attachment.url"
                                class="h-36 w-36 rounded-xl object-cover shadow-sm sm:h-44 sm:w-44"
                            />
                        </a>
                    </div>
                    <p
                        v-if="message.body"
                        :class="[
                            'max-w-xs break-words rounded-2xl px-4 py-2 shadow-sm',
                            isMe ? 'bg-red-500 text-white rounded-br-md' : 'bg-gray-200 text-gray-900 rounded-bl-md',
                        ]"
                    >
                        {{ message.body }}
                    </p>
                </template>

                <template v-else-if="message.type === 'location' && message.location">
                    <a
                        v-if="mapsUrl"
                        :href="mapsUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block font-medium underline underline-offset-2"
                    >
                        {{ message.location.label || message.location.address || 'Vị trí được chia sẻ' }}
                    </a>
                    <p v-if="message.location.address" class="mt-1 text-xs opacity-80">
                        {{ message.location.address }}
                    </p>
                    <p class="mt-1 text-xs opacity-80">
                        {{ message.location.latitude }}, {{ message.location.longitude }}
                    </p>
                </template>

                <template v-else>
                    {{ message.body }}
                </template>
            </div>

            <div :class="['flex items-center gap-1 text-xs text-gray-500 px-1', isMe ? 'justify-end' : '']">
                <span>{{ timeText }}</span>
            </div>
        </div>
    </div>
</template>
