<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Giá trị cốt lõi</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-900">Những điều định hình Sukientot.com</h2>
            </div>
            <div class="mt-8 grid gap-6 sm:grid-cols-2">
                <article
                    v-for="value in values"
                    :key="value.title"
                    class="rounded-3xl border border-slate-100 bg-slate-50/80 p-6 shadow-sm shadow-black/[0.03]">
                    <h3 class="text-xl font-semibold text-slate-900">{{ value.title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ value.description }}
                    </p>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
interface CoreValue {
    title: string;
    description: string;
}

defineProps<{
    values: CoreValue[];
}>();
</script>
