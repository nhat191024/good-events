<script setup lang="ts">
    import { ref, computed } from 'vue'
    import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
    import ChatSidebar from './components/ChatSidebar.vue'
    import ChatDetail from './components/ChatDetail.vue'

    const selectedChatId = ref<string | null>(null)
    const isMobileMenuOpen = ref(false)

    const sidebarClass = computed(() => [
        selectedChatId.value && !isMobileMenuOpen.value ? 'hidden md:block' : 'block',
        'w-full md:w-80 md:min-w-80 md:max-w-80 flex-shrink-0 border-r border-border',
    ])
</script>

<template>
    <div class="flex flex-col h-screen bg-background bg-white">
        <ClientAppHeaderLayout>
            <div class="flex flex-1 overflow-hidden w-full">
                <!-- sidebar -->
                <div :class="sidebarClass">
                    <ChatSidebar :selected-chat-id="selectedChatId" @select="(id) => (selectedChatId = id)"
                        @close-mobile-menu="() => (isMobileMenuOpen = false)" />
                </div>

                <!-- chat detail -->
                <div v-if="selectedChatId" class="flex-1 flex flex-col min-w-0">
                    <ChatDetail :chat-id="selectedChatId" @back="() => (selectedChatId = null)"
                        @toggle-mobile-menu="() => (isMobileMenuOpen = !isMobileMenuOpen)" />
                </div>

                <!-- placeholder desktop khi chưa chọn chat -->
                <div v-else class="hidden md:flex flex-1 items-center justify-center text-muted-foreground">
                    <div class="text-center">
                        <h3 class="text-lg font-medium mb-2">Chọn một cuộc trò chuyện</h3>
                        <p class="text-sm">Chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu</p>
                    </div>
                </div>
            </div>
        </ClientAppHeaderLayout>
    </div>
</template>
