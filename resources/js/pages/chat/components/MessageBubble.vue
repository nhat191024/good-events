<script setup lang="ts">
import { computed } from 'vue'
import type { ChatMessage } from '../types'

const props = defineProps<{ message: ChatMessage; showAvatar?: boolean; avatarUrl?: string }>()

const isMe = computed(() => props.message.senderId === 'me')

const timeText = computed(() =>
    props.message.timestamp.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }),
)
</script>

<template>
    <div :class="['flex gap-2', isMe ? 'justify-end' : 'justify-start']">
        <img v-if="!isMe && (showAvatar ?? true)" :src="avatarUrl" alt="avatar"
            class="h-6 w-6 mt-1 rounded-full object-cover" />
        <div v-else-if="!isMe" class="h-6 w-6 mt-1"></div>

        <div :class="['max-w-[75%] space-y-1', isMe ? 'items-end' : '']">
            <div :class="[
                'px-4 py-2 rounded-2xl text-sm leading-relaxed shadow-sm',
                isMe ? 'bg-red-500 text-white rounded-br-md' : 'bg-muted text-foreground rounded-bl-md',
            ]">
                {{ message.content }}
            </div>

            <div :class="['flex items-center gap-1 text-xs text-muted-foreground px-1', isMe ? 'justify-end' : '']">
                <span>{{ timeText }}</span>
                <span v-if="isMe" class="text-xs">{{ message.isRead ? '✓✓' : '✓' }}</span>
            </div>
        </div>
    </div>
</template>
