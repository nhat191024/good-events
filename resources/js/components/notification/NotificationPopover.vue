<script setup lang="ts">
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'
import { Bell, RefreshCw } from 'lucide-vue-next'
import { NotiItem } from '.'
import { formatDate, formatTime } from '@/lib/helper';

/* two-way binding mở/đóng */
const open = defineModel<boolean>('open', { default: false })

/* props */
const props = withDefaults(defineProps<{
    items: NotiItem[]
    loading?: boolean
    emptyText?: string
    placement?: 'bottom-end' | 'bottom-start'
}>(), {
    items: () => [],
    loading: false,
    emptyText: 'Chưa có thông báo nào',
    placement: 'bottom-end',
})

/* events */
const emit = defineEmits<{
    (e: 'select', item: NotiItem): void
    (e: 'mark-all-read'): void
    (e: 'see-all'): void
    (e: 'reload'): void
}>()

/* refs */
const containerRef = ref<HTMLElement>()
const panelRef = ref<HTMLElement>()

/* computed */
const unreadCount = computed(() => props.items.filter(i => i.unread).length)

/* methods */
function toggle(event: MouseEvent) {
    event.stopPropagation()
    open.value = !open.value
}

function onClickItem(item: NotiItem) {
    emit('select', item)
    open.value = false
}

function onClickOutside(event: MouseEvent) {
    if (!containerRef.value) return

    const target = event.target as Node
    if (!containerRef.value.contains(target)) {
        open.value = false
    }
}

/* handle click outside */
watch(open, async (isOpen) => {
    await nextTick()
    if (isOpen) {
        document.addEventListener('click', onClickOutside)
    } else {
        document.removeEventListener('click', onClickOutside)
    }
})

/* cleanup */
onBeforeUnmount(() => {
    document.removeEventListener('click', onClickOutside)
})
</script>

<template>
    <div ref="containerRef" class="relative inline-block self-start">
        <!-- Trigger Button -->
        <button type="button"
            class="bg-white/70 relative inline-flex items-center justify-center h-10 w-10 rounded-full hover:bg-muted/60 transition-colors"
            @click="toggle" aria-label="Thông báo">
            <Bell class="h-5 w-5" />
            <span v-if="unreadCount"
                class="absolute -top-1 -right-0 h-4 min-w-[16px] px-1 bg-red-500 rounded-full flex items-center justify-center">
                <span class="text-[10px] leading-none text-white font-bold">
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </span>
        </button>

        <!-- Popover Panel -->
        <Transition enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <div v-if="open" ref="panelRef"
                class="ring-1 ring-primary-200 ring-opacity-5 absolute mt-2 w-80 bg-popover text-popover-foreground border border-border rounded-lg shadow-lg overflow-hidden z-[100]"
                :class="placement === 'bottom-end' ? 'right-0' : 'left-0'">
                <!-- Header -->
                <div class="p-4 border-b border-border flex items-center justify-between bg-background">
                    <h3 class="font-semibold">Thông báo</h3>
                    <div class="flex items-center gap-2">
                        <button type="button"
                            class="h-8 w-8 inline-flex items-center justify-center rounded-md border border-transparent hover:border-border transition-colors disabled:opacity-50"
                            @click.stop="$emit('reload')" :disabled="loading" aria-label="Tải lại thông báo">
                            <RefreshCw class="h-4 w-4"
                                :class="loading ? 'animate-spin text-primary' : 'text-muted-foreground'" />
                        </button>
                        <span v-if="unreadCount"
                            class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary font-medium">
                            {{ unreadCount }} mới
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="max-h-96 overflow-y-auto">
                    <!-- Loading State -->
                    <div v-if="loading"
                        class="p-8 flex flex-col items-center justify-center gap-2 text-muted-foreground">
                        <RefreshCw class="h-5 w-5 animate-spin" />
                        <span class="text-sm">Đang tải...</span>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!items.length"
                        class="p-8 text-sm text-muted-foreground flex flex-col items-center justify-center gap-3">
                        <Bell class="h-8 w-8 opacity-50" />
                        <span>{{ emptyText }}</span>
                    </div>

                    <!-- Notification List -->
                    <div v-else class="py-2">
                        <button v-for="item in items" :key="item.id"
                            class="w-full text-left px-4 py-3 hover:bg-muted/50 transition-colors relative group"
                            @click="onClickItem(item)">
                            <div class="flex items-start gap-3">
                                <!-- Unread Indicator -->
                                <div class="mt-1.5 h-2 w-2 rounded-full flex-shrink-0 transition-opacity"
                                    :class="item.unread ? 'bg-primary' : 'opacity-0'" />

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium line-clamp-1"
                                        :class="item.unread ? 'text-foreground' : 'text-muted-foreground'">
                                        {{ item.title }}
                                    </div>
                                    <div v-if="item.message" class="text-xs text-muted-foreground mt-0.5 line-clamp-2">
                                        {{ item.message }}
                                    </div>
                                    <div v-if="item.created_at" class="text-[11px] text-muted-foreground mt-1">
                                        {{ formatTime(item.created_at) }} - {{ formatDate(item.created_at) }}
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div v-if="items.length > 0" class="p-3 border-t border-border flex items-center gap-2 bg-background">
                    <button type="button"
                        class="flex-1 text-sm h-8 rounded-md border border-border hover:bg-muted transition-colors"
                        @click="$emit('mark-all-read')">
                        Đánh dấu đã đọc
                    </button>
                    <!-- <button
                        type="button"
                        class="flex-1 text-sm h-8 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                        @click="$emit('see-all')"
                    >
                        Xem tất cả
                    </button> -->
                </div>
            </div>
        </Transition>
    </div>
</template>
