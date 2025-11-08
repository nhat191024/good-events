<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-8 shadow-sm shadow-black/[0.03]">
                <div class="text-center">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Câu hỏi thường gặp</p>
                    <h2 class="mt-3 text-3xl font-bold text-slate-900">Bạn đang thắc mắc điều gì?</h2>
                </div>
                <div class="mt-8 space-y-4">
                    <details
                        v-for="faq in faqs"
                        :key="faq.question"
                        class="group rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-indigo-200">
                        <summary class="cursor-pointer text-base font-semibold text-slate-900">
                            {{ faq.question }}
                        </summary>
                        <p class="mt-3 text-sm text-slate-600">
                            {{ faq.answer }}
                        </p>
                    </details>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
interface FAQItem {
    question: string;
    answer: string;
}

defineProps<{
    faqs: FAQItem[];
}>();
</script>
