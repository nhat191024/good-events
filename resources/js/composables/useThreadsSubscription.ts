import { onMounted, onUnmounted, Ref } from 'vue'
import type { Thread, BroadcastMessagePayload } from '@/pages/chat/types'

interface UseThreadsSubscriptionOptions {
    threads: Ref<Thread[]>
    selectedThreadId: Ref<number | null>
    onMessageReceived?: (payload: BroadcastMessagePayload) => void
}

export function useThreadsSubscription(options: UseThreadsSubscriptionOptions) {
    const { threads, selectedThreadId, onMessageReceived } = options
    const echo = (window as any).Echo
    const subscribedChannels = new Set<number>()

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
        if (!echo || subscribedChannels.has(threadId)) return

        try {
            const channel = echo.private(`thread.${threadId}`)

            channel.listen('SendMessage', (payload: BroadcastMessagePayload) => {
                handleBroadcastMessage(payload)
            })

            channel.subscribed(() => {
                subscribedChannels.add(threadId)
            })

            channel.error((error: any) => {
                console.error(`❌ Subscription error for thread.${threadId}:`, error)
            })
        } catch (error) {
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
