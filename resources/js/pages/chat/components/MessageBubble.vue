<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { PhoneIncoming, PhoneOutgoing } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Message } from '../types';

const props = defineProps<{ message: Message }>();

const page = usePage();

const currentUserId = computed(() => {
    return (page.props.auth as any)?.user?.id || null;
});

const isMe = computed(() => props.message.user_id === currentUserId.value);

const isImageMessage = computed(() => props.message.type === 'image');

const isCallMessage = computed(() => props.message.type === 'call');

const callTitle = computed(() => (isMe.value ? 'Cuộc gọi đi' : 'Cuộc gọi đến'));

const callDuration = computed(() => {
    const totalSeconds = Math.max(0, Math.floor(props.message.call?.duration_seconds ?? 0));
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    const parts: string[] = [];

    if (hours > 0) {
        parts.push(`${hours} giờ`);
    }

    if (minutes > 0) {
        parts.push(`${minutes} phút`);
    }

    if (seconds > 0 || parts.length === 0) {
        parts.push(`${seconds} giây`);
    }

    return parts.join(' ');
});

const timeText = computed(() => {
    const date = new Date(props.message.created_at);
    return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
});

const mapsUrl = computed(() => {
    if (!props.message.location) {
        return null;
    }

    return `https://www.google.com/maps?q=${props.message.location.latitude},${props.message.location.longitude}`;
});
</script>

<template>
    <div :class="['flex gap-2', isMe ? 'justify-end' : 'justify-start']">
        <div :class="['max-w-[75%] space-y-1', isMe ? 'items-end' : '']">
            <div v-if="!isMe" class="mb-1 px-1 text-xs text-gray-600">
                {{ message.user.name }}
            </div>

            <div
                :class="[
                    'text-sm leading-relaxed',
                    isImageMessage || isCallMessage ? 'space-y-2' : 'rounded-2xl px-4 py-2 shadow-sm',
                    !isImageMessage && !isCallMessage && (isMe ? 'rounded-br-md bg-red-500 text-white' : 'rounded-bl-md bg-gray-200 text-gray-900'),
                ]"
            >
                <template v-if="message.type === 'call'">
                    <div
                        :class="[
                            'flex min-w-56 items-center gap-3 rounded-2xl px-4 py-3 shadow-sm',
                            isMe ? 'rounded-br-md bg-red-500 text-white' : 'rounded-bl-md bg-gray-200 text-gray-900',
                        ]"
                    >
                        <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-full', isMe ? 'bg-white/20' : 'bg-white']">
                            <PhoneOutgoing v-if="isMe" class="h-5 w-5" aria-hidden="true" />
                            <PhoneIncoming v-else class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium">{{ callTitle }}</p>
                            <p :class="['text-xs', isMe ? 'text-white/80' : 'text-gray-600']">{{ callDuration }}</p>
                        </div>
                    </div>
                </template>

                <template v-else-if="message.type === 'image'">
                    <div :class="['inline-grid gap-2 align-top', message.attachments.length > 1 ? 'grid-cols-2' : 'grid-cols-1']">
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
                            'max-w-xs rounded-2xl px-4 py-2 break-words shadow-sm',
                            isMe ? 'rounded-br-md bg-red-500 text-white' : 'rounded-bl-md bg-gray-200 text-gray-900',
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
                    <p class="mt-1 text-xs opacity-80">{{ message.location.latitude }}, {{ message.location.longitude }}</p>
                </template>

                <template v-else>
                    {{ message.body }}
                </template>
            </div>

            <div :class="['flex items-center gap-1 px-1 text-xs text-gray-500', isMe ? 'justify-end' : '']">
                <span>{{ timeText }}</span>
            </div>
        </div>
    </div>
</template>
