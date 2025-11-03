<script setup lang="ts">
    import { ref, computed, inject, onMounted, Ref } from 'vue'
    import { Search, MoreVertical } from 'lucide-vue-next'
    import { router } from '@inertiajs/vue3'
    import axios from 'axios'
    import { createSearchFilter } from '@/lib/search-filter'
    import { debounce } from '@/pages/orders/helper'
    import type { Thread } from '../types'

    const props = defineProps<{ selectedThreadId: number | null }>()
    const emit = defineEmits<{ (e: 'select', id: number): void; (e: 'closeMobileMenu'): void }>()

    const threads = inject<Ref<Thread[]>>('threads')!
    const hasMoreThreads = inject<Ref<boolean>>('hasMoreThreads')!
    const subscribeToThread = inject<(threadId: number) => void>('subscribeToThread')!

    const searchTerm = ref('')
    const currentPage = ref(1)
    const isLoading = ref(false)
    const threadsContainer = ref<HTMLDivElement | null>(null)

    const searchKeys = ['subject', 'other_participants', 'latest_message.body']

    const filteredThreads = computed(() => {
        const query = searchTerm.value.trim()
        if (!query) {
            return threads.value
        }

        const filter = createSearchFilter<Thread>(searchKeys, query)
        return threads.value.filter(filter)
    })

    function handleSelect(id: number) {
        emit('select', id)
        emit('closeMobileMenu')

        // Mark thread as read in UI
        const thread = threads.value.find(t => t.id === id)
        if (thread) {
            thread.is_unread = false
        }
        
    }

    async function loadMoreThreads() {
        if (isLoading.value || !hasMoreThreads.value) return

        isLoading.value = true
        currentPage.value++

        try {
            const response = await axios.get('/chat/threads', {
                params: {
                    page: currentPage.value,
                    search: searchTerm.value,
                },
            })

            const newThreads = response.data.data
            threads.value.push(...newThreads)
            hasMoreThreads.value = response.data.hasMore

            newThreads.forEach((thread: Thread) => {
                subscribeToThread(thread.id)
            })
        } catch (error) {
            console.error('Error loading threads:', error)
        } finally {
            isLoading.value = false
        }
    }

    async function searchThreads() {
        currentPage.value = 1
        isLoading.value = true

        try {
            const response = await axios.get('/chat/threads', {
                params: {
                    page: 1,
                    search: searchTerm.value,
                },
            })

            threads.value = response.data.data
            hasMoreThreads.value = response.data.hasMore
        } catch (error) {
            console.error('Error searching threads:', error)
        } finally {
            isLoading.value = false
        }
    }

    const debouncedSearchThreads = debounce(() => {
        searchThreads()
    }, 3000, { leading: false, trailing: true })

    function formatTimestamp(timestamp: string): string {
        const date = new Date(timestamp)
        const now = new Date()
        const diffInMs = now.getTime() - date.getTime()
        const diffInMinutes = Math.floor(diffInMs / 60000)
        const diffInHours = Math.floor(diffInMs / 3600000)
        const diffInDays = Math.floor(diffInMs / 86400000)

        if (diffInMinutes < 1) return 'Vừa xong'
        if (diffInMinutes < 60) return `${diffInMinutes} phút trước`
        if (diffInHours < 24) return `${diffInHours} giờ trước`
        if (diffInDays < 7) return `${diffInDays} ngày trước`

        return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' })
    }

    onMounted(() => {
        const container = threadsContainer.value
        if (container) {
            container.addEventListener('scroll', () => {
                if (isLoading.value) return

                if (container.scrollTop + container.clientHeight >= container.scrollHeight - 100) {
                    if (hasMoreThreads.value) {
                        loadMoreThreads()
                    }
                }
            })
        }
    })
</script>

<template>
    <div class="flex flex-col h-full bg-white">
        <!-- header + search -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-900">Tin nhắn</h2>
                <button class="p-2 rounded hover:bg-gray-100" aria-label="more">
                    <MoreVertical class="h-4 w-4" />
                </button>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input v-model="searchTerm" type="text" placeholder="Tìm kiếm cuộc hội thoại..." @input="debouncedSearchThreads"
                    class="pl-10 w-full bg-gray-50 border-0 focus:outline-none focus:ring-1 ring-red-500 px-3 py-2 rounded-md" />
            </div>
        </div>

        <!-- list -->
        <div ref="threadsContainer" class="flex-1 overflow-y-auto">
            <button v-for="thread in filteredThreads" :key="thread.id"
                class="w-full flex items-start gap-3 p-4 cursor-pointer hover:bg-gray-50 transition-all duration-200 border-l-2 text-left"
                :class="props.selectedThreadId === thread.id ? 'bg-red-50 border-l-red-500' : 'border-l-transparent'"
                @click="handleSelect(thread.id)">
                <!-- info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-medium text-gray-900 truncate pr-2">
                            {{ thread.subject || 'Không có tiêu đề' }}
                        </h3>
                        <span class="text-xs text-gray-500 flex-shrink-0">
                            {{ formatTimestamp(thread.updated_at) }}
                        </span>
                    </div>

                    <p v-if="thread.other_participants.length > 0" class="text-sm text-gray-600 truncate mb-1">
                        {{thread.other_participants.map(p => p.name).join(', ')}}
                    </p>

                    <p v-if="thread.latest_message" class="text-sm text-gray-500 truncate">
                        {{ thread.latest_message.body }}
                    </p>
                </div>

                <span v-if="thread.is_unread"
                    class="inline-flex items-center justify-center h-5 min-w-5 text-xs px-2 rounded-full bg-red-500 text-white font-medium">
                    Mới
                </span>
            </button>

            <!-- Loading more indicator -->
            <div v-if="isLoading" class="flex items-center justify-center py-4">
                <div class="animate-spin h-5 w-5 border-2 border-red-500 border-t-transparent rounded-full"></div>
                <span class="ml-2 text-sm text-gray-600">Đang tải thêm...</span>
            </div>

            <!-- Empty state -->
            <div v-if="filteredThreads.length === 0 && !isLoading" class="p-8 text-center text-gray-500">
                <Search class="h-8 w-8 mx-auto mb-2 opacity-50" />
                <p class="text-sm">Chưa có cuộc hội thoại nào</p>
            </div>
        </div>
    </div>
</template>
