import { Ref } from "vue";
import { OrderStatus } from "../types";

/**
 ** slow down spamming of function calls, example use in other component:
 ** const debouncedReload = debounce(() => doSomething(), 500, { leading: true, trailing: false })
 ** 500ms: delay in ms
 ** leading: run first, then delay
 ** trailing: delay first, then run
 ** both: run first, then delay, then run once more
 * @param fn (callable)
 * @param wait (ms)
 * @param options? (optional)
 * @returns 
 */
export function debounce<T extends (...args: any[]) => void>(
    fn: T,
    wait = 300,
    options: { leading?: boolean; trailing?: boolean } = { leading: true, trailing: false }
) {
    let timeout: number | undefined
    let lastArgs: any
    let invoked = false

    return (...args: Parameters<T>) => {
        lastArgs = args

        if (!timeout) {
            if (options.leading && !invoked) {
                fn(...args)
                invoked = true
            }

            timeout = window.setTimeout(() => {
                timeout = undefined
                invoked = false
                if (options.trailing && lastArgs) {
                    fn(...lastArgs)
                    lastArgs = null
                }
            }, wait)
        }
    }
}

type SlowFn<T extends (...args: any[]) => void> = ((...args: Parameters<T>) => void) & { clear: () => void }

/**
 * Enqueue calls and run them sequentially on a fixed interval.
 *
 * ```ts
 * const enqueueSave = slow(saveDraft, 200)
 * enqueueSave(payloadA) // runs immediately
 * enqueueSave(payloadB) // runs ~200ms later
 * enqueueSave.clear()   // flush the queue and cancel pending timeouts
 * ```
 * @param fn Callback to execute serially.
 * @param wait Delay in ms between each queued execution. Default 100.
 */
export function slow<T extends (...args: any[]) => void>(fn: T, wait = 100): SlowFn<T> {
    const queue: Parameters<T>[] = []
    let isRunning = false
    let timeoutId: number | undefined

    function runNext() {
        if (!queue.length) {
            isRunning = false
            timeoutId = undefined
            return
        }

        isRunning = true
        const args = queue.shift()!

        timeoutId = window.setTimeout(() => {
            timeoutId = undefined
            fn(...args)
            runNext()
        }, wait)
    }

    const runner = ((...args: Parameters<T>) => {
        queue.push(args)
        if (!isRunning) {
            runNext()
        }
    }) as SlowFn<T>

    runner.clear = () => {
        queue.length = 0
        if (timeoutId !== undefined) {
            window.clearTimeout(timeoutId)
            timeoutId = undefined
        }
        isRunning = false
    }

    return runner
}

export function statusBadge(status: OrderStatus) {
    switch (status) {
        case OrderStatus.PENDING:
            return { text: 'Đang chờ', cls: 'bg-yellow-100 text-yellow-800 border border-yellow-200', border_class: 'border-l-yellow-200' }
        case OrderStatus.CONFIRMED:
            return { text: 'Đã chốt', cls: 'bg-blue-100 text-blue-800 border border-blue-200', border_class: 'border-l-blue-200' }
        case OrderStatus.EXPIRED:
            return { text: 'Hết hạn', cls: 'bg-orange-100 text-orange-800 border border-orange-200', border_class: 'border-l-orange-200' }
        case OrderStatus.IN_JOB:
            return { text: 'Đã đến nơi', cls: 'bg-primary-100 text-primary-800 border border-primary-200', border_class: 'border-l-primary-200' }
        case OrderStatus.COMPLETED:
            return { text: 'Hoàn thành', cls: 'bg-green-100 text-green-800 border border-green-200', border_class: 'border-l-green-200' }
        case OrderStatus.CANCELLED:
            return { text: 'Đã hủy', cls: 'bg-red-100 text-red-800 border border-red-200', border_class: 'border-l-red-200' }
        default:
            return { text: 'Không rõ', cls: 'bg-muted text-foreground border border-border', border_class: 'border-l-border' }
    }
}

export function parseNextPage(payload: any, altParam = 'page'): number | null {
    if (payload?.meta) {
        const { current_page, last_page } = payload.meta
        return current_page < last_page ? current_page + 1 : null
    }
    if (payload?.links?.next) {
        const nextUrl = new URL(payload.links.next)

        const nextPageParam = nextUrl.searchParams.get(altParam) ?? nextUrl.searchParams.get('page')
        return nextPageParam ? parseInt(nextPageParam) : null
    }
    return null
}

export function stripPagingFromUrl(params: string[] = ['page', 'history_page']) {
    const url = new URL(window.location.href)
    params.forEach(p => url.searchParams.delete(p))
    window.history.replaceState({}, '', url)
}

export function appendUniqueById<T extends { id: number | string }>(
    target: Ref<T[]>,
    items: T[],
    loadedIds: Ref<Set<number | string>>,
): number {
    const unique = items.filter(i => !loadedIds.value.has(i.id))
    if (unique.length) {
        unique.forEach(i => loadedIds.value.add(i.id))
        target.value.push(...unique)
    }
    return unique.length
}

export function prependUniqueById<T extends { id: number | string }>(
    target: Ref<T[]>,
    items: T[],
    loadedIds: Ref<Set<number | string>>,
): number {
    const unique = items.filter(i => !loadedIds.value.has(i.id))
    if (unique.length) {
        target.value = [...unique, ...target.value]
        unique.forEach(i => loadedIds.value.add(i.id))
    }
    return unique.length
}
