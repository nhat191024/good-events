import { reactive } from 'vue'

interface PageLoadingState {
    isLoading: boolean
    progress: number | null
}

const state = reactive<PageLoadingState>({
    isLoading: false,
    progress: null,
})

let timeout: ReturnType<typeof setTimeout> | null = null
const DELAY = 0

export function startPageLoading() {
    if (timeout) {
        clearTimeout(timeout)
    }

    timeout = setTimeout(() => {
        state.isLoading = true
        state.progress = 0
    }, DELAY)
}

export function setPageProgress(percentage: number) {
    if (state.isLoading) {
        state.progress = Math.min(Math.max(percentage, 0), 100)
    }
}

export function finishPageLoading() {
    if (timeout) {
        clearTimeout(timeout)
        timeout = null
    }

    if (state.isLoading) {
        state.isLoading = false
        state.progress = null
    }
}

export function resetPageLoading() {
    state.progress = 0
}

export function isPageLoadingStarted() {
    return state.isLoading
}

export function usePageLoadingState() {
    return state
}
