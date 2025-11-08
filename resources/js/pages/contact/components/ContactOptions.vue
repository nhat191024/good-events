<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <article
                    v-for="channel in channels"
                    :key="channel.title"
                    class="rounded-3xl border border-slate-100 bg-slate-50/80 p-6 shadow-sm shadow-black/[0.03]">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">{{ channel.title }}</p>
                    <p class="mt-3 text-base text-slate-600">
                        {{ channel.description }}
                    </p>
                    <p class="mt-4 text-lg font-semibold text-slate-900">{{ channel.detail }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ channel.hint }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
interface ContactChannel {
    title: string;
    description: string;
    detail: string;
    hint: string;
}

defineProps<{
    channels: ContactChannel[];
}>();
</script>
