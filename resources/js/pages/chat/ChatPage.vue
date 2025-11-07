<script setup lang="ts">
import { useThreadsSubscription } from '@/composables/useThreadsSubscription';
import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import { computed, provide, ref } from 'vue';
import ChatDetail from './components/ChatDetail.vue';
import ChatSidebar from './components/ChatSidebar.vue';
import type { Thread } from './types';

interface Props {
    initialThreads: Thread[];
    hasMoreThreads: boolean;
    initialSelectedChatId?: number | null;
}

const props = defineProps<Props>();

const threads = ref<Thread[]>(props.initialThreads);
const hasMoreThreads = ref(props.hasMoreThreads);

const isValidThreadId = computed(() => {
    return threads.value.some((thread) => thread.id == props.initialSelectedChatId);
});

const selectedThreadId = ref<number | null>(isValidThreadId.value ? (props.initialSelectedChatId as number) : null);

const isMobileMenuOpen = ref(false);

const { subscribeToThread } = useThreadsSubscription({
    threads,
    selectedThreadId,
});

// Provide threads state to child components
provide('threads', threads);
provide('hasMoreThreads', hasMoreThreads);
provide('selectedThreadId', selectedThreadId);
provide('subscribeToThread', subscribeToThread);

const sidebarClass = computed(() => [
    selectedThreadId.value && !isMobileMenuOpen.value ? 'hidden md:block' : 'block',
    'w-full md:w-80 md:min-w-80 md:max-w-80 flex-shrink-0 border-r border-gray-200 h-full',
]);

function handleSelectThread(id: number) {
    selectedThreadId.value = id;
    isMobileMenuOpen.value = false;

    const thread = threads.value.find((t) => t.id === id);

    if (thread) {
        thread.is_unread = false;
    }
}

function handleBack() {
    selectedThreadId.value = null;
}

function handleToggleMobileMenu() {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
}
</script>

<template>
    <ClientAppHeaderLayout :show-footer="false">
        <div class="flex w-full overflow-hidden" style="height: calc(98vh - 4rem)">
            <div :class="sidebarClass">
                <ChatSidebar
                    :selected-thread-id="selectedThreadId"
                    @select="handleSelectThread"
                    @close-mobile-menu="() => (isMobileMenuOpen = false)"
                />
            </div>

            <div v-if="selectedThreadId" class="flex min-w-0 flex-1 flex-col">
                <ChatDetail :thread-id="selectedThreadId" @back="handleBack" @toggle-mobile-menu="handleToggleMobileMenu" />
            </div>

            <div v-else class="hidden flex-1 items-center justify-center text-gray-500 md:flex">
                <div class="text-center">
                    <h3 class="mb-2 text-lg font-medium text-gray-700">Chọn một cuộc trò chuyện</h3>
                    <p class="text-sm">Chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu</p>
                </div>
            </div>
        </div>

        <!-- Pusher Debug Component -->
        <!-- <PusherDebug /> -->
    </ClientAppHeaderLayout>
</template>
