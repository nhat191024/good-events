<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <article class="rounded-3xl border border-slate-100 bg-slate-50/70 p-6 shadow-sm shadow-black/[0.02]">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Nguyên tắc vận hành</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Mọi thành viên đều minh bạch và cam kết chuẩn</h2>
                    <ul class="mt-4 space-y-4 text-sm text-slate-600">
                        <li
                            v-for="principle in principles"
                            :key="principle"
                            class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500" />
                            <span>{{ principle }}</span>
                        </li>
                    </ul>
                </article>

                <article class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm shadow-black/[0.02]">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Cam kết & trách nhiệm</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Bảo vệ lợi ích của khách hàng và nhân sự</h2>
                    <ul class="mt-4 space-y-4 text-sm text-slate-600">
                        <li
                            v-for="commitment in commitments"
                            :key="commitment"
                            class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500" />
                            <span>{{ commitment }}</span>
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
defineProps<{
    principles: string[];
    commitments: string[];
}>();
</script>
