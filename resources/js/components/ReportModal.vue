<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import {
    DialogRoot,
    DialogPortal,
    DialogOverlay,
    DialogContent,
    DialogTitle,
    DialogDescription,
    DialogClose,
} from 'reka-ui'
import { showToast } from '@/composables/useToast'
import { watch } from 'vue'
import Icon from './Icon.vue';
import Input from '@/components/ui/input/Input.vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
    open: boolean
    userId?: number
    billId?: number
    billCode?: string
}>()

const emit = defineEmits(['update:open', 'close'])

const form = useForm({
    reported_user_id: props.userId,
    reported_bill_id: props.billId,
    title: '',
    description: '',
})

watch(() => props.open, (newVal) => {
    if (newVal) {
        form.reported_user_id = props.userId
        form.reported_bill_id = props.billId
        form.title = ''
        form.description = ''
        form.clearErrors()
    }
})

function submit() {
    const url = props.billId ? route('report.bill') : route('report.user')

    form
        .transform((data) => ({
            ...data,
            reported_user_id: props.billId ? undefined : data.reported_user_id
        }))
        .post(url, {
            onSuccess: () => {
                const page = usePage()
                const flash = (page.props as any).flash
                const error = flash?.error
                const success = flash?.success

                // Global toast handles flash messages (error and success)
                // We just need to control the modal state based on success

                if (!error && success) {
                    emit('update:open', false)
                    emit('close')
                }
            },
            onError: (errors: any) => {
                const msg = errors.error || errors.report_error || 'Có lỗi xảy ra khi gửi báo cáo'
                showToast({ message: msg, type: 'error' })
            }
        })
}

function close() {
    emit('update:open', false)
    emit('close')
}
</script>

<template>
    <DialogRoot :open="open" @update:open="emit('update:open', $event)">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm" />
            <DialogContent
                class="fixed top-[50%] left-[50%] max-w-[500px] w-[90vw] translate-x-[-50%] translate-y-[-50%] rounded-xl bg-white p-6 shadow-xl z-[999] focus:outline-none ring ring-primary-500">
                <div class="flex items-center justify-between mb-4">
                    <DialogTitle class="text-lg font-bold text-gray-900">
                        {{ billId ? `Báo cáo đơn hàng${billCode ? ' - ' + billCode : ''}` : 'Báo cáo người dùng' }}
                    </DialogTitle>
                    <DialogClose class="text-gray-400 hover:text-gray-500 transition-colors" @click="close">
                        <Icon name="x" class="w-5 h-5" />
                    </DialogClose>
                </div>

                <DialogDescription class="text-sm text-gray-500 mb-4">
                    Vui lòng cung cấp chi tiết về vấn đề bạn đang gặp phải. Chúng tôi sẽ xem xét và xử lý sớm nhất có
                    thể.
                </DialogDescription>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
                        <Input id="title" v-model="form.title" type="text"
                            :class="{ 'border-red-500': form.errors.title }" placeholder="Nhập tiêu đề báo cáo" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Nội dung chi
                            tiết</label>
                        <textarea id="description" v-model="form.description" rows="4" :class="cn(
                            'placeholder:text-muted-foreground selection:bg-primary-700 selection:text-white dark:bg-input/30 border-gray-300 flex w-full min-w-0 rounded-md border px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                            'focus-visible:border-primary-400 focus-visible:border-3',
                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                            { 'border-red-500': form.errors.description }
                        )" placeholder="Mô tả chi tiết vấn đề..."></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description
                            }}</p>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="close"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Hủy bỏ
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center gap-2">
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Gửi báo cáo
                        </button>
                    </div>
                </form>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
