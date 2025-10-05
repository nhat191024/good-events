<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Search } from 'lucide-vue-next'
import NotificationPopover from '@/components/notification/NotificationPopover.vue'
import { NotiItem } from '@/components/notification'

// State
const showNotiPopover = ref(false)
const notiLoading = ref(false)

// Sample data
const notiItems = ref<NotiItem[]>([
    {
        id: 1,
        title: 'Đơn hàng đã chốt',
        message: "Đơn 'MC sự kiện' đã được chốt thành công",
        unread: true,
        created_at: 'Vừa xong'
    },
    {
        id: 2,
        title: 'Ứng viên mới',
        message: "Có 1 ứng viên mới cho vị trí 'Chủ hệ hoạt náo'",
        unread: false,
        created_at: '2 giờ trước'
    },
])

// Methods
async function fetchNotifications() {
    notiLoading.value = true
    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000))

        // In real app, fetch from API
        // const response = await fetch('/api/notifications')
        // notiItems.value = await response.json()

        console.log('Fetching notifications...')
    } catch (error) {
        console.error('Error fetching notifications:', error)
    } finally {
        notiLoading.value = false
    }
}

function handleSelectNotification(item: NotiItem) {
    // Mark as read
    const index = notiItems.value.findIndex(n => n.id === item.id)
    if (index !== -1) {
        notiItems.value[index].unread = false
    }

    // Navigate if href provided
    if (item.href) {
        // router.push(item.href)
        console.log('Navigate to:', item.href)
    }
}

function handleMarkAllRead() {
    notiItems.value = notiItems.value.map(item => ({
        ...item,
        unread: false
    }))
    console.log('All notifications marked as read')
}

function handleSeeAll() {
    // Navigate to notifications page
    // router.push('/notifications')
    console.log('Navigate to all notifications')
    showNotiPopover.value = false
}

// Fetch notifications on mount
onMounted(() => {
    fetchNotifications()
})
</script>

<template>
    <header class="fixed top-0 left-0 right-0 z-40 bg-card border-b border-border">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Left Section -->
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-primary">Sukientot</h1>

                <!-- Search Bar -->
                <div class="relative hidden">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground h-4 w-4" />
                    <input
                        placeholder="Tìm kiếm đơn hàng, ứng viên..."
                        class="pl-10 w-80 h-9 rounded-md bg-input border border-border text-sm px-3 focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-all"
                        type="text"
                    />
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center gap-4">
                <!-- Notification Popover -->
                <NotificationPopover
                    v-model:open="showNotiPopover"
                    :items="notiItems"
                    :loading="notiLoading"
                    placement="bottom-end"
                    empty-text="Không có thông báo mới"
                    @select="handleSelectNotification"
                    @mark-all-read="handleMarkAllRead"
                    @see-all="handleSeeAll"
                    @reload="fetchNotifications"
                />

                <!-- Add Avatar Menu here -->
                <!-- <AvatarMenu /> -->
            </div>
        </div>
    </header>
</template>
