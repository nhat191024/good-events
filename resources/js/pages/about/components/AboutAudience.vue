<template>
    <section class="bg-slate-50">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Đối tượng sử dụng</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-900">Ai đang đồng hành cùng Sukientot.com?</h2>
                <p class="mx-auto mt-3 max-w-3xl text-base text-slate-600">
                    Chúng tôi tạo nên hành trình thống nhất cho cả khách hàng, nhân sự biểu diễn và đội ngũ quản trị, đảm bảo mọi bên đều được hỗ trợ tối đa.
                </p>
            </div>
            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                <article
                    v-for="audience in audiences"
                    :key="audience.title"
                    class="rounded-3xl border border-white bg-white p-6 shadow-sm shadow-black/[0.03]">
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ audience.category }}</p>
                    <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ audience.title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ audience.description }}
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li
                            v-for="benefit in audience.benefits"
                            :key="benefit"
                            class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500" />
                            <span>{{ benefit }}</span>
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
interface Audience {
    category: string;
    title: string;
    description: string;
    benefits: string[];
}

defineProps<{
    audiences: Audience[];
}>();
</script>
