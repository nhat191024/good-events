<script setup lang="ts">
    import { ref, watch, nextTick, computed, onMounted, onUnmounted, inject, Ref } from 'vue'
    import { ArrowLeft, MoreVertical, Send } from 'lucide-vue-next'
    import { usePage } from '@inertiajs/vue3'
    import axios from 'axios';
    import type { Message, Thread, ThreadDetail, BroadcastMessagePayload } from '../types'
    import MessageBubble from './MessageBubble.vue'

    const props = defineProps<{ threadId: number }>()
    const emit = defineEmits<{ (e: 'back'): void; (e: 'toggleMobileMenu'): void }>()

    const threads = inject<Ref<Thread[]>>('threads')!
    const selectedThreadId = inject<Ref<number | null>>('selectedThreadId')!

    const page = usePage()

    const echo = (window as any).Echo

    const thread = ref<ThreadDetail | null>(null)
    const messages = ref<Message[]>([])
    const newMessage = ref('')
    const messagesContainer = ref<HTMLDivElement | null>(null)
    const endRef = ref<HTMLDivElement | null>(null)
    const isLoadingMessages = ref(false)
    const hasMoreMessages = ref(false)
    const messagesCurrentPage = ref(1)
    const isSending = ref(false)

    const currentThread = computed(() => {
        return threads.value.find(t => t.id === props.threadId)
    })

    const currentUserId = computed(() => {
        return (page.props.auth as any)?.user?.id || null
    })

    watch(() => props.threadId, async (newId) => {
        if (newId) {
            await loadThread(newId)
        }
    }, { immediate: true })

    async function loadThread(threadId: number) {
        messagesCurrentPage.value = 1
        isLoadingMessages.value = true

        try {
            const response = await axios.get(`/chat/threads/${threadId}/messages`, {
                params: { page: 1 }
            })

            messages.value = response.data.data
            hasMoreMessages.value = response.data.hasMore
            thread.value = response.data.thread

            await nextTick()
            scrollToBottom()

            // Subscribe to real-time updates
            subscribeToThread(threadId)
        } catch (error) {
            console.error('Error loading thread:', error)
        } finally {
            isLoadingMessages.value = false
        }
    }

    async function loadMoreMessages() {
        if (isLoadingMessages.value || !hasMoreMessages.value || !props.threadId) return

        isLoadingMessages.value = true
        messagesCurrentPage.value++

        const container = messagesContainer.value
        const oldScrollHeight = container?.scrollHeight || 0
        const oldScrollTop = container?.scrollTop || 0

        try {
            const response = await axios.get(`/chat/threads/${props.threadId}/messages`, {
                params: { page: messagesCurrentPage.value }
            })

            messages.value = [...response.data.data, ...messages.value]
            hasMoreMessages.value = response.data.hasMore

            await nextTick()

            if (container) {
                const heightDifference = container.scrollHeight - oldScrollHeight
                container.scrollTop = oldScrollTop + heightDifference
            }
        } catch (error) {
            console.error('Error loading more messages:', error)
        } finally {
            isLoadingMessages.value = false
        }
    }

    async function sendMessage() {
        if (!newMessage.value.trim() || isSending.value || !props.threadId) return

        isSending.value = true
        const messageBody = newMessage.value
        newMessage.value = ''

        try {
            const response = await axios.post(`/chat/threads/${props.threadId}/messages`, {
                body: messageBody
            })

            if (response.data.success) {
                // Add message immediately to UI
                const newMsg: Message = response.data.message
                messages.value.push(newMsg)

                await nextTick()
                scrollToBottom()
            }
        } catch (error) {
            console.error('Error sending message:', error)
            // Restore message on error
            newMessage.value = messageBody
        } finally {
            isSending.value = false
        }
    }

    function onKeydown(e: KeyboardEvent) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault()
            sendMessage()
        }
    }

    function scrollToBottom() {
        nextTick(() => {
            endRef.value?.scrollIntoView({ behavior: 'smooth' })
        })
    }

    function subscribeToThread(threadId: number) {
        if (!echo) {
            console.error('❌ Cannot subscribe: Echo not available')
            return
        }

        try {
            const channel = echo.private(`thread.${threadId}`)

            channel.listen('SendMessage', (payload: BroadcastMessagePayload) => {
                handleBroadcastMessage(payload)
            })

            // Listen for subscription success
            channel.subscribed(() => {
                //
            })

            // Listen for subscription errors
            channel.error((error: any) => {
                console.error(`❌ Subscription error for thread.${threadId}:`, error)
            })
        } catch (error) {
            console.error('❌ Error subscribing to thread:', error)
        }
    }

    function handleBroadcastMessage(payload: BroadcastMessagePayload) {
        const threadId = payload.message.thread_id
        const senderId = payload.sender_id

        if (senderId === currentUserId.value) return

        if (threadId === props.threadId) {
            const newMsg: Message = {
                id: payload.message.id,
                thread_id: payload.message.thread_id,
                user_id: payload.message.user_id,
                body: payload.message.body,
                created_at: payload.message.created_at,
                updated_at: payload.message.updated_at,
                user: payload.user,
            }

            messages.value.push(newMsg)

            nextTick(() => {
                scrollToBottom()
            })
        }
    }

    function formatDate(dateString: string): string {
        const date = new Date(dateString)
        const today = new Date()
        const yesterday = new Date(today)
        yesterday.setDate(yesterday.getDate() - 1)

        if (date.toDateString() === today.toDateString()) {
            return 'Hôm nay'
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Hôm qua'
        } else {
            return date.toLocaleDateString('vi-VN', {
                weekday: 'long',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            })
        }
    }

    onMounted(() => {
        const container = messagesContainer.value
        if (container) {
            container.addEventListener('scroll', () => {
                if (isLoadingMessages.value) return

                if (container.scrollTop <= 100) {
                    if (hasMoreMessages.value) {
                        loadMoreMessages()
                    }
                }
            })
        }
    })

    onUnmounted(() => {
        if (echo && props.threadId) {
            echo.leave(`thread.${props.threadId}`)
        }
    })
</script>

<template>
    <div class="flex flex-col h-full bg-white">
        <!-- header -->
        <div class="border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center gap-3">
                    <button class="p-2 md:hidden hover:bg-gray-200 rounded" @click="emit('back')" aria-label="back">
                        <ArrowLeft class="h-4 w-4" />
                    </button>

                    <div>
                        <h2 class="font-medium text-gray-900">
                            {{ thread?.subject || currentThread?.subject || 'Không có tiêu đề' }}
                        </h2>
                        <p v-if="thread?.participants" class="text-xs text-gray-600">
                            {{thread.participants.map(p => p.name).join(', ')}}
                        </p>
                    </div>
                </div>
                <button class="p-2 hover:bg-gray-200 rounded" aria-label="more">
                    <MoreVertical class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- messages -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Loading older messages -->
            <div v-if="isLoadingMessages && hasMoreMessages" class="flex items-center justify-center py-2">
                <div class="animate-spin h-5 w-5 border-2 border-red-500 border-t-transparent rounded-full"></div>
                <span class="ml-2 text-sm text-gray-600">Đang tải tin nhắn cũ...</span>
            </div>

            <div class="flex flex-col gap-2 w-full h-fit ring ring-primary rounded-md bg-primary-50 p-3 text-xs md:text-sm text-center text-gray-500">
                <p>
                    <b>Bảo mật thông tin: </b>Vui lòng không chia sẻ số điện thoại, zalo hay thông tin
                    cá nhân khác để đảm bảo an toàn cho chính bạn.
                    giao dịch qua ứng dụng : mọi thỏa thuận về giá cả, công việc phát sinh hoặc
                    thay đổi lịch hẹn đều cần được xác nhận trên ứng dụng 
                </p>

                <p>
                    <b>Đảm bảo quyền lợi: </b>sukientot.com chỉ bảo vệ và hỗ trợ các vấn đề 
                    (bảo hành, khiếu nại) dựa trên các giao dịch được ghi nhận chính thức trên
                    ứng dụng.
                </p>
                
                <p>
                    <b>Báo cáo vi phạm:</b> Nếu CTV sukientot.com có bất kỳ yêu cầu giao dịch 
                    riêng nào, hãy báo cáo ngay cho chúng tôi qua hotline <b>0393719095</b>.
                </p>


            </div>

            <!-- Messages list -->
            <template v-for="(message, idx) in messages" :key="message.id">
                <!-- Date separator -->
                <div v-if="idx === 0 || new Date(messages[idx - 1].created_at).toDateString() !== new Date(message.created_at).toDateString()"
                    class="flex justify-center py-2">
                    <div class="bg-gray-100 px-3 py-1 rounded-full text-xs text-gray-600">
                        {{ formatDate(message.created_at) }}
                    </div>
                </div>

                <MessageBubble :message="message" />
            </template>

            <div ref="endRef" />
        </div>

        <!-- input area -->
        <div class="border-t border-gray-200 p-4 bg-white">
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <input v-model="newMessage" @keydown="onKeydown" placeholder="Nhập tin nhắn..."
                        :disabled="isSending"
                        class="w-full bg-gray-50 border-0 focus:outline-none focus:ring-1 ring-red-500 rounded-full px-4 py-2 disabled:opacity-50"
                        type="text" />
                </div>

                <button
                    class="p-2 rounded-full bg-red-500 hover:bg-red-600 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    :disabled="!newMessage.trim() || isSending" @click="sendMessage" aria-label="send">
                    <Send class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</template>
