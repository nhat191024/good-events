<template>
    <section class="bg-slate-50">
        <div class="mx-auto w-full max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <motion.div
                class="rounded-3xl border border-slate-200 bg-white p-8 shadow-lg shadow-black/[0.04]"
                :initial="cardMotion.initial"
                :while-in-view="cardMotion.visible"
                :viewport="cardMotion.viewport"
                :transition="cardMotion.transition"
                :while-hover="cardInteractions.hover"
                :while-tap="cardInteractions.tap"
            >
                <div class="grid gap-8 lg:grid-cols-5">
                    <div class="space-y-3 lg:col-span-2">
                        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Gửi yêu cầu</p>
                        <h2 class="text-3xl font-bold text-slate-900">Đặt lịch tư vấn với chuyên gia</h2>
                        <p class="text-base text-slate-600">
                            Chúng tôi sẽ liên hệ để trao đổi chi tiết và đề xuất giải pháp phù hợp nhất với đội ngũ của bạn.
                        </p>
                        <ul class="space-y-2 text-sm text-slate-500">
                            <li>• Thời gian phản hồi tối đa 24 giờ.</li>
                            <li>• Ưu tiên các yêu cầu kèm thông tin ngân sách & timeline dự kiến.</li>
                        </ul>
                    </div>
                    <form class="space-y-4 lg:col-span-3" @submit.prevent="submitForm">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block text-sm font-medium text-slate-700">
                                Họ và tên
                                <input
                                    v-model="form.fullName"
                                    type="text"
                                    required
                                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                />
                            </label>
                            <label class="block text-sm font-medium text-slate-700">
                                Email công việc
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                />
                            </label>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block text-sm font-medium text-slate-700">
                                Số điện thoại
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                />
                            </label>
                            <label class="block text-sm font-medium text-slate-700">
                                Chủ đề
                                <select
                                    v-model="form.topic"
                                    required
                                    class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                    <option disabled value="">Chọn chủ đề</option>
                                    <option
                                        v-for="topic in topics"
                                        :key="topic.value"
                                        :value="topic.value">
                                        {{ topic.label }}
                                    </option>
                                </select>
                            </label>
                        </div>
                        <label class="block text-sm font-medium text-slate-700">
                            Nội dung
                            <textarea
                                v-model="form.message"
                                rows="4"
                                required
                                class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                            />
                        </label>
                        <motion.button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-6 py-3 text-base font-semibold text-white"
                            :while-hover="buttonInteractions.hover"
                            :while-tap="buttonInteractions.tap"
                        >
                            Gửi thông tin
                        </motion.button>
                        <p v-if="submitted" class="text-sm text-emerald-600">Cảm ơn bạn! Chúng tôi sẽ liên hệ sớm nhất.</p>
                    </form>
                </div>
            </motion.div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { reactive, ref } from 'vue';

interface TopicOption {
    value: string;
    label: string;
}

const props = defineProps<{
    topics: TopicOption[];
}>();

const form = reactive({
    fullName: '',
    email: '',
    phone: '',
    topic: '',
    message: '',
});

const submitted = ref(false);

function submitForm(): void {
    if (!form.topic) {
        form.topic = props.topics[0]?.value ?? '';
    }

    submitted.value = true;
    setTimeout(() => {
        submitted.value = false;
    }, 4000);
}

const cardMotion = {
    initial: { opacity: 0, y: 40 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const cardInteractions = {
    hover: {
        scale: 1.01,
        y: -4,
        transition: { type: 'spring', duration: 0.55, bounce: 0.3 },
    },
    tap: {
        scale: 0.99,
        transition: { type: 'spring', duration: 0.4, bounce: 0.2 },
    },
} as const;

const buttonInteractions = {
    hover: {
        scale: 1.02,
        y: -2,
        transition: { type: 'spring', duration: 0.45, bounce: 0.3 },
    },
    tap: {
        scale: 0.96,
        transition: { type: 'spring', duration: 0.35, bounce: 0.25 },
    },
} as const;
</script>
