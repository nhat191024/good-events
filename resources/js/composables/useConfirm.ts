import { reactive } from 'vue'

type ConfirmState = {
    open: boolean
    title: string
    message: string
    okText: string
    cancelText: string
    resolve?: (v: boolean) => void
}

const state = reactive<ConfirmState>({
    open: false,
    title: '',
    message: '',
    okText: 'Yes',
    cancelText: 'No',
    resolve: undefined,
})

export function confirm(opts: {
    title?: string
    message?: string
    okText?: string
    cancelText?: string
} = {}) {
    state.title = opts.title ?? 'Xác nhận'
    state.message = opts.message ?? ''
    state.okText = opts.okText ?? 'Có'
    state.cancelText = opts.cancelText ?? 'Huỷ'
    state.open = true

    return new Promise<boolean>((res) => {
        state.resolve = (v: boolean) => {
            res(v)
            // reset state a bit (keep fields for next time)
            state.open = false
            state.resolve = undefined
        }
    })
}

export function useConfirmState() {
    return state
}
