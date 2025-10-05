<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { ArrowLeft, Menu, MoreVertical, Smile, ImageIcon, MapPin, Send } from 'lucide-vue-next'
import type { Chat, ChatMessage } from '../types'
import MessageBubble from './MessageBubble.vue'

const PLACEHOLDER_IMG = 'https://framerusercontent.com/images/df2MITpCi9OZn8p6nlIcEBe6otE.png?scale-down-to=512&width=1280&height=1280'

const props = defineProps<{ chatId: string }>()
const emit = defineEmits<{ (e: 'back'): void; (e: 'toggleMobileMenu'): void }>()

const chat = ref<Chat>({
    id: '1',
    user: { id: '1', name: 'Chủ hệ', avatar: '', isOnline: true },
    lastMessage: 'bạn thành toàn cọc nha',
    timestamp: '1 phút trước',
    unreadCount: 0,
    product: { id: '1', name: 'Chủ hệ văn bóng chuyên khu vực Hà Nội', price: '2.345.000 đ', image: '' },
})

const activeTab = ref<'chat' | 'details'>('chat')
const messages = ref<ChatMessage[]>([
    { id: '1', content: 'bạn đặt show chủ hệ văn bóng a?', senderId: 'other', timestamp: new Date('2025-01-11T16:10:00'), isRead: true },
    { id: '2', content: 'đúng r bạn, đặt như thế nào ấy b nhi', senderId: 'me', timestamp: new Date('2025-01-11T16:14:00'), isRead: true },
    { id: '3', content: 'Mình cần thuê cho sự kiện vào cuối tuần này, khoảng 50 người tham gia', senderId: 'other', timestamp: new Date('2025-01-11T16:15:00'), isRead: true },
    { id: '4', content: 'Ok bạn, mình có thể cung cấp đầy đủ thiết bị. Giá thuê sẽ là 2.345.000đ cho cả bộ', senderId: 'me', timestamp: new Date('2025-01-11T16:16:00'), isRead: true },
])
const newMessage = ref('')
const isTyping = ref(false)
const inputRef = ref<HTMLInputElement | null>(null)
const endRef = ref<HTMLDivElement | null>(null)

const scrollToBottom = () => nextTick(() => endRef.value?.scrollIntoView({ behavior: 'smooth' }))
watch(messages, scrollToBottom, { deep: true })

watch(newMessage, (v) => {
    if (!v) return
    isTyping.value = true
    setTimeout(() => (isTyping.value = false), 1000)
})

function handleSend() {
    if (!newMessage.value.trim()) return
    messages.value.push({ id: Date.now().toString(), content: newMessage.value, senderId: 'me', timestamp: new Date(), isRead: false })
    newMessage.value = ''
    inputRef.value?.focus()
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        handleSend()
    }
}
</script>

<template>
    <div class="flex flex-col h-full bg-background">
        <!-- header user + product -->
        <div class="border border-border border-x-0 bg-card bg-white">
            <div class="flex items-center justify-between p-4 pb-2">
                <div class="flex items-center gap-3">
                    <button class="p-2 md:hidden" @click="emit('back')" aria-label="back">
                        <ArrowLeft class="h-4 w-4" />
                    </button>
                    <button class="p-2 md:hidden" @click="emit('toggleMobileMenu')" aria-label="menu">
                        <Menu class="h-4 w-4" />
                    </button>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <img :src="chat.user.avatar || PLACEHOLDER_IMG" :alt="chat.user.name"
                                class="h-8 w-8 rounded-full object-cover" />
                            <div v-if="chat.user.isOnline"
                                class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 bg-green-500 border border-background rounded-full" />
                        </div>
                        <div>
                            <h2 class="font-medium text-foreground">{{ chat.user.name }}</h2>
                            <p class="text-xs text-green-600">Đang hoạt động</p>
                        </div>
                    </div>
                </div>
                <button class="p-2" aria-label="more">
                    <MoreVertical class="h-4 w-4" />
                </button>
            </div>

            <!-- product info -->
            <div v-if="chat.product" class="px-4 pb-3">
                <div class="flex items-center gap-3 p-3 bg-muted rounded-lg">
                    <img :src="chat.product.image || PLACEHOLDER_IMG" :alt="chat.product.name"
                        class="h-12 w-12 rounded-lg object-cover" />
                    <div class="flex-1 min-w-0">
                        <h3 class="font-medium text-foreground text-sm truncate">{{ chat.product.name }}</h3>
                        <p class="text-sm font-semibold text-primary">{{ chat.product.price }}</p>
                    </div>
                </div>
            </div>

            <!-- tabs siêu nhẹ -->
            <div class="w-full">
                <div class="grid grid-cols-2">
                    <button class="pb-3 data-[active=true]:border-b-2 data-[active=true]:border-primary"
                        :data-active="activeTab === 'chat'" @click="activeTab = 'chat'">Chat</button>
                    <button class="pb-3 data-[active=true]:border-b-2 data-[active=true]:border-primary"
                        :data-active="activeTab === 'details'" @click="activeTab = 'details'">Chi tiết</button>
                </div>
            </div>
        </div>

        <!-- nội dung -->
        <div class="flex-1 overflow-hidden">
            <div v-show="activeTab === 'chat'" class="h-full">
                <!-- day badge -->
                <div class="flex justify-center py-4">
                    <div class="bg-muted px-3 py-1 rounded-full text-xs text-muted-foreground">Thứ 6, 11/07/2025</div>
                </div>

                <!-- messages list -->
                <div class="flex-1 overflow-y-auto px-4 space-y-4 pb-4 max-h-full">
                    <MessageBubble v-for="(m, idx) in messages" :key="m.id" :message="m"
                        :show-avatar="idx === 0 || messages[idx - 1].senderId !== m.senderId"
                        :avatar-url="PLACEHOLDER_IMG" />

                    <!-- typing indicator -->
                    <div v-if="isTyping" class="flex gap-2 justify-start">
                        <img :src="PLACEHOLDER_IMG" class="h-6 w-6 mt-1 rounded-full object-cover" alt="u" />
                        <div class="bg-muted px-3 py-2 rounded-2xl rounded-bl-md">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-muted-foreground rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-muted-foreground rounded-full animate-bounce"
                                    style="animation-delay:.1s"></div>
                                <div class="w-2 h-2 bg-muted-foreground rounded-full animate-bounce"
                                    style="animation-delay:.2s"></div>
                            </div>
                        </div>
                    </div>

                    <div ref="endRef" />
                </div>

                <!-- input area -->
                <div class="border-t border-border p-4 bg-card bg-white">
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <input ref="inputRef" v-model="newMessage" @keydown="onKeydown"
                                placeholder="Nhập tin nhắn..."
                                class="w-full bg-muted/50 border-0 focus-visible:ring-1 ring-ring rounded-full px-4 py-2"
                                type="text" />
                        </div>

                        <div class="flex items-center gap-1">
                            <button class="p-2 text-muted-foreground hover:text-foreground" aria-label="emoji">
                                <Smile class="h-4 w-4" />
                            </button>
                            <button class="p-2 text-muted-foreground hover:text-foreground" aria-label="image">
                                <ImageIcon class="h-4 w-4" />
                            </button>
                            <button class="p-2 text-muted-foreground hover:text-foreground" aria-label="pin">
                                <MapPin class="h-4 w-4" />
                            </button>
                            <button
                                class="p-2 rounded-full bg-red-500 hover:bg-red-600 text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="!newMessage.trim()" @click="handleSend" aria-label="send">
                                <Send class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-2 text-xs text-muted-foreground">
                        <button class="flex items-center gap-1 hover:text-foreground transition-colors">
                            <ImageIcon class="h-3 w-3" />Ảnh & video
                        </button>
                        <button class="flex items-center gap-1 hover:text-foreground transition-colors">
                            <MapPin class="h-3 w-3" />Địa chỉ
                        </button>
                    </div>
                </div>
            </div>

            <div v-show="activeTab === 'details'" class="h-full p-8 flex items-center justify-center text-center">
                <div>
                    <h3 class="text-lg font-medium text-foreground mb-2">Chi tiết cuộc trò chuyện</h3>
                    <p class="text-muted-foreground">Chi tiết sẽ được điền vào đây</p>
                    <p class="text-xs text-muted-foreground mt-2">Chat ID: {{ props.chatId }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
