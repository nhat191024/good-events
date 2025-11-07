<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <article
                    v-for="section in sections"
                    :key="section.title"
                    class="rounded-3xl border border-slate-100 bg-slate-50/70 p-6 shadow-sm shadow-black/[0.03]">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">{{ section.eyebrow }}</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">{{ section.title }}</h2>
                    <p class="mt-3 text-base text-slate-600">
                        {{ section.description }}
                    </p>
                    <ul v-if="section.points?.length" class="mt-4 space-y-2 text-sm text-slate-600">
                        <li
                            v-for="point in section.points"
                            :key="point"
                            class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500" />
                            <span>{{ point }}</span>
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
interface OverviewSection {
    eyebrow: string;
    title: string;
    description: string;
    points?: string[];
}

defineProps<{
    sections: OverviewSection[];
}>();
</script>
