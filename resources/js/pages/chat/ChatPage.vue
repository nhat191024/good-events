<script setup lang="ts">
    import { ref, computed, provide } from 'vue'
    import { usePage } from '@inertiajs/vue3'
    import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
    import ChatSidebar from './components/ChatSidebar.vue'
    import ChatDetail from './components/ChatDetail.vue'
    import PusherDebug from '@/components/PusherDebug.vue'
    import type { Thread, BroadcastMessagePayload } from './types'
    import { useThreadsSubscription } from '@/composables/useThreadsSubscription'

    interface Props {
        initialThreads: Thread[]
        hasMoreThreads: boolean
    }

    const props = defineProps<Props>()

    const threads = ref<Thread[]>(props.initialThreads)
    const hasMoreThreads = ref(props.hasMoreThreads)
    const selectedThreadId = ref<number | null>(null)
    const isMobileMenuOpen = ref(false)

    // Subscribe to all threads for real-time updates
    const { subscribeToThread } = useThreadsSubscription({
        threads,
        selectedThreadId,
        onMessageReceived: (payload: BroadcastMessagePayload) => {
            // This will be called for all messages across all threads
            console.log('💬 Global message received:', payload)
        }
    })

    // Provide threads state to child components
    provide('threads', threads)
    provide('hasMoreThreads', hasMoreThreads)
    provide('selectedThreadId', selectedThreadId)
    provide('subscribeToThread', subscribeToThread)

    const sidebarClass = computed(() => [
        selectedThreadId.value && !isMobileMenuOpen.value ? 'hidden md:block' : 'block',
        'w-full md:w-80 md:min-w-80 md:max-w-80 flex-shrink-0 border-r border-gray-200 h-full',
    ])

    function handleSelectThread(id: number) {
        selectedThreadId.value = id
        isMobileMenuOpen.value = false

        // Mark as read when selected
        const thread = threads.value.find(t => t.id === id)
        if (thread) {
            thread.is_unread = false
        }
    }

    function handleBack() {
        selectedThreadId.value = null
    }

    function handleToggleMobileMenu() {
        isMobileMenuOpen.value = !isMobileMenuOpen.value
    }
</script>

<template>
    <ClientAppHeaderLayout :show-footer="false">
        <!-- Chat container với chiều cao cố định = viewport height - header height -->
        <div class="flex overflow-hidden w-full" style="height: calc(100vh - 4rem);">
            <!-- sidebar -->
            <div :class="sidebarClass">
                <ChatSidebar :selected-thread-id="selectedThreadId" @select="handleSelectThread"
                    @close-mobile-menu="() => (isMobileMenuOpen = false)" />
            </div>

            <!-- chat detail -->
            <div v-if="selectedThreadId" class="flex-1 flex flex-col min-w-0">
                <ChatDetail :thread-id="selectedThreadId" @back="handleBack"
                    @toggle-mobile-menu="handleToggleMobileMenu" />
            </div>

            <!-- placeholder desktop khi chưa chọn chat -->
            <div v-else class="hidden md:flex flex-1 items-center justify-center text-gray-500">
                <div class="text-center">
                    <h3 class="text-lg font-medium mb-2 text-gray-700">Chọn một cuộc trò chuyện</h3>
                    <p class="text-sm">Chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu</p>
                </div>
            </div>
        </div>

        <!-- Pusher Debug Component -->
        <!-- <PusherDebug /> -->
    </ClientAppHeaderLayout>
</template>
