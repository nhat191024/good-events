import { onMounted, onUnmounted, Ref } from 'vue'
import type { Thread, BroadcastMessagePayload } from '@/pages/chat/types'
import { slow } from '@/pages/orders/helper'

interface UseThreadsSubscriptionOptions {
    threads: Ref<Thread[]>
    selectedThreadId: Ref<number | null>
    onMessageReceived?: (payload: BroadcastMessagePayload) => void
}

//? avoid spamming, you could try to set this to 0ms for testing (with more than 10 chat threads on the sidebar)
const SUBSCRIBE_DELAY_MS = 500

export function useThreadsSubscription(options: UseThreadsSubscriptionOptions) {
    const { threads, selectedThreadId, onMessageReceived } = options
    const echo = (window as any).Echo
    const subscribedChannels = new Set<number>()
    const pendingSubscriptions = new Set<number>()

    const scheduleSubscribe = slow((threadId: number) => {
        performSubscribe(threadId)
    }, SUBSCRIBE_DELAY_MS)

    function subscribeToThreads() {
        if (!echo) {
            console.error('❌ Echo not available for threads subscription')
            return
        }

        // Subscribe to all threads
        threads.value.forEach(thread => {
            subscribeToThread(thread.id)
        })
    }

    function subscribeToThread(threadId: number) {
        if (!echo || subscribedChannels.has(threadId) || pendingSubscriptions.has(threadId)) {
            return
        }

        pendingSubscriptions.add(threadId)
        scheduleSubscribe(threadId)
    }

    function performSubscribe(threadId: number) {
        if (!echo) {
            pendingSubscriptions.delete(threadId)
            return
        }

        if (subscribedChannels.has(threadId)) {
            pendingSubscriptions.delete(threadId)
            return
        }

        try {
            const channel = echo.private(`thread.${threadId}`)

            channel.listen('SendMessage', (payload: BroadcastMessagePayload) => {
                handleBroadcastMessage(payload)
            })

            channel.subscribed(() => {
                pendingSubscriptions.delete(threadId)
                subscribedChannels.add(threadId)
            })

            channel.error((error: any) => {
                pendingSubscriptions.delete(threadId)
                console.error(`❌ Subscription error for thread.${threadId}:`, error)
            })
        } catch (error) {
            pendingSubscriptions.delete(threadId)
            console.error(`❌ Error subscribing to thread.${threadId}:`, error)
        }
    }

    function handleBroadcastMessage(payload: BroadcastMessagePayload) {
        const threadId = payload.message.thread_id
        const messageBody = payload.message.body
        const createdAt = payload.message.created_at

        // Update thread in list
        updateThreadInList(threadId, messageBody, createdAt)

        // Call custom handler if provided
        if (onMessageReceived) {
            onMessageReceived(payload)
        }
    }

    function updateThreadInList(threadId: number, messageBody: string, createdAt: string) {
        const threadIndex = threads.value.findIndex(t => t.id === threadId)
        if (threadIndex === -1) return

        const threadToUpdate = { ...threads.value[threadIndex] }

        threadToUpdate.latest_message = {
            body: messageBody,
            created_at: createdAt,
        }
        threadToUpdate.updated_at = createdAt

        if (threadId !== selectedThreadId.value) {
            threadToUpdate.is_unread = true
        }

        threads.value.splice(threadIndex, 1)
        threads.value.unshift(threadToUpdate)
    }

    function unsubscribeFromThreads() {
        if (!echo) return

        subscribedChannels.forEach(threadId => {
            try {
                echo.leave(`thread.${threadId}`)
            } catch (error) {
                console.error(`Error unsubscribing from thread.${threadId}:`, error)
            }
        })

        subscribedChannels.clear()
        pendingSubscriptions.clear()
        scheduleSubscribe.clear()
    }

    onMounted(() => {
        subscribeToThreads()
    })

    onUnmounted(() => {
        unsubscribeFromThreads()
    })

    return {
        subscribeToThread,
        subscribeToThreads,
        unsubscribeFromThreads,
    }
}
