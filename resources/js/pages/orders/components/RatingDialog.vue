<script setup lang="ts">
import { Star } from 'lucide-vue-next'

// 3 model hai chiều: showRatingDialog, rating, comment
// tên trong defineModel phải khớp với v-model:xxx ở parent (kebab-case hay camel-case đều ok)
const showRatingDialog = defineModel<boolean>('showRatingDialog', { default: false })
const rating = defineModel<number>('rating', { default: 0 })
const comment = defineModel<string>('comment', { default: '' })

// emit thêm sự kiện submit để parent xử lý lưu
const emit = defineEmits<{
    (e: 'submit', payload: { rating: number; comment: string }): void
}>()

function submitRating() {
    emit('submit', { rating: rating.value, comment: comment.value })
    // đóng dialog và reset local model
    showRatingDialog.value = false
    rating.value = 0
    comment.value = ''
}
</script>

<template>
    <div v-if="showRatingDialog" class="fixed inset-0 z-[60] grid place-items-center bg-black/40 p-4"
        @click.self="showRatingDialog = false">
        <div class="w-full max-w-md rounded-xl bg-card text-card-foreground border border-border shadow-lg">
            <div class="p-4 border-b border-border">
                <div class="flex items-center gap-2 font-semibold">
                    <Star class="h-5 w-5 text-yellow-500" />
                    Đánh giá đơn hàng
                </div>
            </div>

            <div class="p-4 space-y-4">
                <div>
                    <label class="text-sm font-medium mb-2 block">Đánh giá đối tác (1-5 sao)</label>
                    <div class="flex gap-1">
                        <button v-for="s in 5" :key="s" class="p-1 hover:scale-110 transition-transform"
                            @click="rating = s">
                            <Star
                                :class="['h-6 w-6', s <= rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300']" />
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium mb-2 block">Nhận xét về dịch vụ</label>
                    <textarea v-model="comment"
                        class="w-full min-h-[100px] rounded-md bg-input border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                        placeholder="chia sẻ trải nghiệm của bạn..." />
                </div>

                <div class="flex gap-3">
                    <button class="h-9 rounded-md bg-primary-700 text-white flex-1 disabled:opacity-50"
                        :disabled="rating === 0" @click="submitRating()">
                        Gửi đánh giá
                    </button>
                    <button class="h-9 rounded-md border border-border flex-1 bg-transparent"
                        @click="showRatingDialog = false">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
