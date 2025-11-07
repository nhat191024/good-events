<template>
    <section class="bg-slate-50">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-white/80 p-8 shadow-sm shadow-black/[0.04]">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <article
                        v-for="stat in stats"
                        :key="stat.label"
                        class="rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm shadow-black/[0.03]">
                        <p class="text-sm font-medium uppercase tracking-wide text-slate-500">{{ stat.label }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ stat.value }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ stat.subtext }}</p>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
interface StatItem {
    label: string;
    value: string;
    subtext: string;
}

defineProps<{
    stats: StatItem[];
}>();
</script>
