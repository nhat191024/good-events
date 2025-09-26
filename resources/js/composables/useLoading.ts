import { reactive } from 'vue'

type LoadingState = {
    loading: boolean
    title: string
    message: string
    cancelable: boolean
}

const state = reactive<LoadingState>({
    loading: false,
    title: '',
    message: '',
    cancelable: false,
})

// keep resolve outside of reactive state
let currentResolve: ((v: boolean) => void) | undefined = undefined

// show loading, returns a promise that resolves when hideLoading is called
export function showLoading(opts: { title?: string; message?: string; cancelable?: boolean } = {}) {
    state.title = opts.title ?? 'Vui lòng chờ'
    state.message = opts.message ?? ''
    state.cancelable = !!opts.cancelable
    state.loading = true

    return new Promise<boolean>((res) => {
        currentResolve = (v: boolean) => {
            res(v)
            // reset minimal fields for next time
            state.loading = false
            state.title = ''
            state.message = ''
            state.cancelable = false
            currentResolve = undefined
        }
    })
}

// hide loading and resolve promise
export function hideLoading(result = true) {
    if (currentResolve) {
        currentResolve(result)
    } else {
        // if nobody is waiting, still reset state
        state.loading = false
        state.title = ''
        state.message = ''
        state.cancelable = false
    }
}

export function useLoadingState() {
    return state
}

// optional: convenience fn that shows then auto-hides after fn finishes
export async function withLoading<T>(
    fn: () => Promise<T>,
    opts?: { title?: string; message?: string }
): Promise<T> {
    const p = showLoading(opts)
    try {
        const r = await fn()
        hideLoading(true)
        await p // ensure the promise resolved (normal flow)
        return r
    } catch (e) {
        hideLoading(false)
        await p
        throw e
    }
}
