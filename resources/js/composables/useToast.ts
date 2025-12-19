import { reactive } from 'vue'

export type ToastType = 'success' | 'error' | 'info' | 'warning'

export interface ToastOptions {
    title?: string
    message: string
    type?: ToastType
    duration?: number
}

export interface Toast extends ToastOptions {
    id: string
}

const state = reactive<{
    toasts: Toast[]
}>({
    toasts: [],
})

let count = 0

function generateId() {
    return `toast_${count++}_${Date.now()}`
}

export function showToast(opts: ToastOptions | string) {
    const options = typeof opts === 'string' ? { message: opts } : opts
    const id = generateId()

    const toast: Toast = {
        id,
        type: 'success',
        duration: 5000,
        ...options,
    }

    // prevent duplicates: if a toast with the same message and type is already visible, don't add another
    const isDuplicate = state.toasts.some(t => t.message === toast.message && t.type === toast.type)
    if (isDuplicate) {
        return state.toasts.find(t => t.message === toast.message && t.type === toast.type)!.id
    }

    state.toasts.push(toast)

    if (toast.duration !== 0) {
        setTimeout(() => {
            removeToast(id)
        }, toast.duration)
    }

    return id
}

export function removeToast(id: string) {
    const index = state.toasts.findIndex((t) => t.id === id)
    if (index > -1) {
        state.toasts.splice(index, 1)
    }
}

export function useToastState() {
    return state
}
