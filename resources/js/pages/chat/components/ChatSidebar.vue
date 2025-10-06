<script setup lang="ts">
import { ref, computed } from 'vue'
import { Search, MoreVertical } from 'lucide-vue-next'
import type { Chat } from '../types'
import { createSearchFilter } from '@/lib/search-filter';

const props = defineProps<{ selectedChatId: string | null }>()
const emit = defineEmits<{ (e: 'select', id: string): void; (e: 'closeMobileMenu'): void }>()

const PLACEHOLDER_IMG = 'https://framerusercontent.com/images/df2MITpCi9OZn8p6nlIcEBe6otE.png?scale-down-to=512&width=1280&height=1280'

const mockChats: Chat[] = [
    {
        id: '1',
        user: { id: '1', name: 'Chủ hệ văn bóng', avatar: '', isOnline: true },
        lastMessage: 'bạn thành toàn cọc nha',
        timestamp: '1 phút trước',
        unreadCount: 0,
        product: { id: '1', name: 'Chủ hệ văn bóng chuyên khu vực Hà Nội', price: '2.345.000 đ', image: '' },
    },
    {
        id: '2',
        user: { id: '2', name: 'Chủ hệ 2', avatar: '', isOnline: false },
        lastMessage: 'chào bạn',
        timestamp: '2 giờ trước',
        unreadCount: 3,
    },
    {
        id: '3',
        user: { id: '3', name: 'Người bán thiết bị thể thao', avatar: '', isOnline: true },
        lastMessage: 'Sản phẩm còn hàng không ạ?',
        timestamp: '5 giờ trước',
        unreadCount: 1,
        product: { id: '2', name: 'Bộ dụng cụ tập gym cao cấp', price: '1.500.000 đ', image: '' },
    },
]

const q = ref('')
const searchColumns = ['user.name', 'lastMessage']

const filtered = computed(() => {
    const filter = createSearchFilter(searchColumns, q.value)
    return mockChats.filter(filter)
})

function handleSelect(id: string) {
    emit('select', id)
    emit('closeMobileMenu')
}
</script>

<template>
    <div class="flex flex-col h-full bg-card bg-white">
        <!-- header + search -->
        <div class="p-4 border border-border border-x-0">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-foreground">Chat</h2>
                <button class="p-2 rounded hover:bg-accent" aria-label="more">
                    <MoreVertical class="h-4 w-4" />
                </button>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <input v-model="q" type="text" placeholder="Tìm kiếm chat"
                    class="pl-10 w-full bg-muted/50 border-0 focus:outline-none focus:ring-1 ring-ring px-3 py-2 rounded-md" />
            </div>
        </div>

        <!-- list -->
        <div class="flex-1 overflow-y-auto">
            <div v-for="chat in filtered" :key="chat.id"
                class="flex items-center gap-3 p-4 cursor-pointer hover:bg-accent/50 transition-all duration-200 border-l-2"
                :class="props.selectedChatId === chat.id ? 'bg-accent border-l-primary' : 'border-l-transparent'"
                @click="handleSelect(chat.id)">
                <!-- avatar + online -->
                <div class="relative flex-shrink-0">
                    <img :src="chat.user.avatar || PLACEHOLDER_IMG" :alt="chat.user.name"
                        class="h-12 w-12 rounded-full object-cover" />
                    <div v-if="chat.user.isOnline"
                        class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 bg-green-500 border-2 border-background rounded-full" />
                </div>

                <!-- info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-medium text-foreground truncate pr-2">{{ chat.user.name }}</h3>
                        <span class="text-xs text-muted-foreground flex-shrink-0">{{ chat.timestamp }}</span>
                    </div>

                    <p class="text-sm text-muted-foreground truncate mb-2">{{ chat.lastMessage }}</p>

                    <div v-if="chat.product" class="flex items-center gap-2 p-2 bg-muted/50 rounded-md">
                        <img :src="chat.product.image || PLACEHOLDER_IMG" :alt="chat.product.name"
                            class="h-8 w-8 rounded object-cover flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-foreground truncate">{{ chat.product.name }}</p>
                            <p class="text-xs text-primary font-semibold">{{ chat.product.price }}</p>
                        </div>
                    </div>
                </div>

                <span v-if="chat.unreadCount > 0"
                    class="inline-flex items-center justify-center h-5 min-w-5 text-xs px-1 rounded-full bg-destructive text-destructive-foreground">{{
                        chat.unreadCount > 99 ? '99+' : chat.unreadCount }}</span>
            </div>

            <div v-if="filtered.length === 0" class="p-8 text-center text-muted-foreground">
                <Search class="h-8 w-8 mx-auto mb-2 opacity-50" />
                <p class="text-sm">Không tìm thấy cuộc trò chuyện nào</p>
            </div>
        </div>
    </div>
</template>
