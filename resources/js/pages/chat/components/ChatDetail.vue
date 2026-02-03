<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ArrowLeft, Send, Flag, MessageCircleQuestionIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed, inject, nextTick, onMounted, onUnmounted, ref, Ref, watch } from 'vue';
import ReportModal from '@/components/ReportModal.vue';
import type { BroadcastMessagePayload, Message, Thread, ThreadDetail } from '../types';
import MessageBubble from './MessageBubble.vue';

const props = defineProps<{ threadId: number }>();
const emit = defineEmits<{ (e: 'back'): void; (e: 'toggleMobileMenu'): void }>();

const threads = inject<Ref<Thread[]>>('threads')!;
const selectedThreadId = inject<Ref<number | null>>('selectedThreadId')!;

const page = usePage();

const echo = (window as any).Echo;

const thread = ref<ThreadDetail | null>(null);
const messages = ref<Message[]>([]);
const newMessage = ref('');
const messagesContainer = ref<HTMLDivElement | null>(null);
const endRef = ref<HTMLDivElement | null>(null);
const isLoadingMessages = ref(false);
const hasMoreMessages = ref(false);
const messagesCurrentPage = ref(1);
const isSending = ref(false);
const isReportModalOpen = ref(false);

let activeChannelThreadId: number | null = null;
let activeChannel: any = null;

function leaveActiveChannel() {
    if (!echo || activeChannelThreadId === null) {
        return;
    }

    try {
        activeChannel?.stopListening?.('SendMessage');
        echo.leave(`thread.${activeChannelThreadId}`);
    } catch (error) {
        console.error(`❌ Error leaving thread.${activeChannelThreadId}:`, error);
    } finally {
        activeChannel = null;
        activeChannelThreadId = null;
    }
}

const currentThread = computed(() => {
    return threads.value.find((t) => t.id === props.threadId);
});

const currentUserId = computed(() => {
    return (page.props.auth as any)?.user?.id || null;
});

watch(
    () => props.threadId,
    async (newId) => {
        if (newId) {
            await loadThread(newId);
        }
    },
    { immediate: true },
);

const reportTargetUserId = computed(() => {
    if (!thread.value && !currentThread.value) return undefined;
    const t = thread.value || currentThread.value;
    // Find the participant that is NOT the current user
    const other = t?.participants?.find((p: any) => p.id !== currentUserId.value);
    return other?.id;
});


async function loadThread(threadId: number) {
    messagesCurrentPage.value = 1;
    isLoadingMessages.value = true;

    try {
        const response = await axios.get(`/chat/threads/${threadId}/messages`, {
            params: { page: 1 },
        });

        messages.value = response.data.data;
        hasMoreMessages.value = response.data.hasMore;
        thread.value = response.data.thread;

        await nextTick();
        scrollToBottom();

        subscribeToThread(threadId);
    } catch (error) {
        console.error('Error loading thread:', error);
    } finally {
        isLoadingMessages.value = false;
    }
}

async function loadMoreMessages() {
    if (isLoadingMessages.value || !hasMoreMessages.value || !props.threadId) return;

    isLoadingMessages.value = true;
    messagesCurrentPage.value++;

    const container = messagesContainer.value;
    const oldScrollHeight = container?.scrollHeight || 0;
    const oldScrollTop = container?.scrollTop || 0;

    try {
        const response = await axios.get(`/chat/threads/${props.threadId}/messages`, {
            params: { page: messagesCurrentPage.value },
        });

        messages.value = [...response.data.data, ...messages.value];
        hasMoreMessages.value = response.data.hasMore;

        await nextTick();

        if (container) {
            const heightDifference = container.scrollHeight - oldScrollHeight;
            container.scrollTop = oldScrollTop + heightDifference;
        }
    } catch (error) {
        console.error('Error loading more messages:', error);
    } finally {
        isLoadingMessages.value = false;
    }
}

async function sendMessage() {
    if (!newMessage.value.trim() || isSending.value || !props.threadId) return;

    isSending.value = true;
    const messageBody = newMessage.value;
    newMessage.value = '';

    try {
        const response = await axios.post(`/chat/threads/${props.threadId}/messages`, {
            body: messageBody,
        });

        if (response.data.success) {
            const newMsg: Message = response.data.message;
            messages.value.push(newMsg);

            await nextTick();
            scrollToBottom();
        }
    } catch (error) {
        console.error('Error sending message:', error);
        newMessage.value = messageBody;
    } finally {
        isSending.value = false;
    }
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}

function subscribeToThread(threadId: number) {
    if (!echo) {
        console.error('❌ Cannot subscribe: Echo not available');
        return;
    }

    if (activeChannelThreadId === threadId) {
        return;
    }

    if (activeChannelThreadId !== null) {
        leaveActiveChannel();
    }

    try {
        const channel = echo.private(`thread.${threadId}`);

        channel.listen('SendMessage', (payload: BroadcastMessagePayload) => {
            handleBroadcastMessage(payload);
        });

        channel.subscribed(() => {
            //
        });

        channel.error((error: any) => {
            console.error(`❌ Subscription error for thread.${threadId}:`, error);
        });

        activeChannel = channel;
        activeChannelThreadId = threadId;
    } catch (error) {
        console.error('❌ Error subscribing to thread:', error);
    }
}

function handleBroadcastMessage(payload: BroadcastMessagePayload) {
    const threadId = Number(payload.message.thread_id);
    const senderId = Number(payload.sender_id);
    const activeThreadId = Number(props.threadId);

    if (Number.isFinite(senderId) && senderId === currentUserId.value) {
        return;
    }

    if (!Number.isFinite(threadId) || !Number.isFinite(activeThreadId)) {
        return;
    }

    if (threadId === activeThreadId) {
        const newMsg: Message = {
            id: payload.message.id,
            thread_id: threadId,
            user_id: payload.message.user_id,
            body: payload.message.body,
            created_at: payload.message.created_at,
            updated_at: payload.message.updated_at,
            user: payload.user,
        };

        messages.value.push(newMsg);

        nextTick(() => {
            scrollToBottom();
        });
    }
}

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
        return 'Hôm nay';
    } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Hôm qua';
    } else {
        return date.toLocaleDateString('vi-VN', {
            weekday: 'long',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    }
}

onMounted(() => {
    const container = messagesContainer.value;
    if (container) {
        container.addEventListener('scroll', () => {
            if (isLoadingMessages.value) return;

            if (container.scrollTop <= 100) {
                if (hasMoreMessages.value) {
                    loadMoreMessages();
                }
            }
        });
    }
});

onUnmounted(() => {
    leaveActiveChannel();
});
</script>

<template>
    <div class="flex h-full flex-col bg-white">
        <!-- header -->
        <div class="border-b border-gray-200 bg-gray-50">
            <div class="p-4">
                <button
                    class="flex items-center gap-2 rounded px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 md:hidden"
                    @click="emit('toggleMobileMenu')" aria-label="Toggle menu">
                    <ArrowLeft class="h-4 w-4" />
                    <span>Trở lại</span>
                </button>
                <div class="flex flex-1 flex-shrink items-center gap-3 not-odd:min-w-0">
                    <div class="min-w-0 flex-1">
                        <h2 class="font-medium break-words text-gray-900">
                            {{ thread?.subject || currentThread?.subject || 'Không có tiêu đề' }}
                        </h2>
                        <p v-if="thread?.participants" class="text-xs text-gray-600">
                            Người tham gia: {{thread.participants.map((p) => p.name).join(', ')}}
                        </p>
                        <p v-if="thread?.bill" class="mt-2 text-xs text-gray-600">Thông tin đơn hàng:</p>
                        <div v-if="thread?.bill" class="text-xs text-gray-600">
                            Sự kiện: {{ thread.bill.event_name || 'N/A' }} <br />
                            Thời gian: {{ thread.bill.datetime || 'N/A' }} <br />
                            Địa điểm: {{ thread.bill.address || 'N/A' }}
                        </div>
                    </div>
                    <Link title="Trợ giúp"
                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                        :href="route('tutorial.index')">
                        <MessageCircleQuestionIcon class="w-5 h-5" />
                    </Link>
                    <button title="Báo cáo"
                        class="cursor-pointer p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                        @click="isReportModalOpen = true">
                        <Flag class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- messages -->
        <div ref="messagesContainer" class="flex-1 space-y-4 overflow-y-auto p-4">
            <!-- Loading older messages -->
            <div v-if="isLoadingMessages && hasMoreMessages" class="flex items-center justify-center py-2">
                <div class="h-5 w-5 animate-spin rounded-full border-2 border-red-500 border-t-transparent"></div>
                <span class="ml-2 text-sm text-gray-600">Đang tải tin nhắn cũ...</span>
            </div>

            <div
                class="flex h-fit w-full flex-col gap-2 rounded-md bg-primary-50 p-3 text-center text-xs text-gray-500 ring ring-primary md:text-sm">
                <p>
                    <b>Bảo mật thông tin: </b>Vui lòng không chia sẻ số điện thoại, zalo hay thông tin cá nhân khác để
                    đảm bảo an toàn cho chính bạn.
                    giao dịch qua ứng dụng : mọi thỏa thuận về giá cả, công việc phát sinh hoặc thay đổi lịch hẹn đều
                    cần được xác nhận trên ứng dụng
                </p>

                <p>
                    <b>Đảm bảo quyền lợi: </b>sukientot.com chỉ bảo vệ và hỗ trợ các vấn đề (bảo hành, khiếu nại) dựa
                    trên các giao dịch được ghi nhận
                    chính thức trên ứng dụng.
                </p>

                <p>
                    <b>Báo cáo vi phạm:</b> Nếu CTV sukientot.com có bất kỳ yêu cầu giao dịch riêng nào, hãy báo cáo
                    ngay cho chúng tôi qua hotline
                    <b>0393719095</b>.
                </p>
            </div>

            <!-- Messages list -->
            <template v-for="(message, idx) in messages" :key="message.id">
                <!-- Date separator -->
                <div v-if="idx === 0 || new Date(messages[idx - 1].created_at).toDateString() !== new Date(message.created_at).toDateString()"
                    class="flex justify-center py-2">
                    <div class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                        {{ formatDate(message.created_at) }}
                    </div>
                </div>

                <MessageBubble :message="message" />
            </template>

            <div ref="endRef" />
        </div>

        <!-- input area -->
        <div class="border-t border-gray-200 bg-white p-4">
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <input v-model="newMessage" @keydown="onKeydown" placeholder="Nhập tin nhắn..."
                        :disabled="isSending"
                        class="w-full rounded-full border-0 bg-gray-50 px-4 py-2 ring-red-500 focus:ring-1 focus:outline-none disabled:opacity-50"
                        type="text" />
                </div>

                <button
                    class="rounded-full bg-red-500 p-2 text-white transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!newMessage.trim() || isSending" @click="sendMessage" aria-label="send">
                    <Send class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
    <ReportModal v-model:open="isReportModalOpen" :user-id="reportTargetUserId" :bill-id="thread?.bill?.id"
        :bill-code="thread?.bill?.code" />
</template>
