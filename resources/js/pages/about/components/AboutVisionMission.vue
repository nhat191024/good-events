<template>
    <section class="bg-slate-900 text-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <article class="rounded-3xl border border-white/10 bg-white/10 p-6 backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-200">Tầm nhìn</p>
                    <h3 class="mt-3 text-2xl font-semibold">{{ vision }}</h3>
                    <p class="mt-4 text-base text-indigo-100">
                        Trở thành lựa chọn đầu tiên khi khách hàng tìm kiếm nhân sự biểu diễn hoặc nhà cung cấp sự kiện.
                    </p>
                </article>
                <article class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-200">Sứ mệnh</p>
                    <ul class="mt-4 space-y-3 text-base text-indigo-100">
                        <li
                            v-for="mission in missionPoints"
                            :key="mission"
                            class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-300" />
                            <span>{{ mission }}</span>
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
defineProps<{
    vision: string;
    missionPoints: string[];
}>();
</script>
