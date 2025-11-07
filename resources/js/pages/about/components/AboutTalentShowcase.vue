<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                <div class="lg:w-1/3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Danh mục nhân sự nổi bật</p>
                    <h2 class="mt-3 text-3xl font-bold text-slate-900">Đầy đủ nhân sự & tiết mục cho mọi sự kiện</h2>
                    <p class="mt-4 text-base text-slate-600">
                        Chỉ cần chọn chủ đề, Sukientot gợi ý đúng nghệ sĩ, tiết mục và workshop phù hợp với ngân sách.
                    </p>
                </div>
                <div class="grid flex-1 gap-4 sm:grid-cols-2">
                    <article
                        v-for="category in categories"
                        :key="category.title"
                        class="rounded-3xl border border-slate-100 bg-slate-50/80 p-5 shadow-sm shadow-black/[0.03]">
                        <h3 class="text-lg font-semibold text-slate-900">{{ category.title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ category.description }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span
                                v-for="tag in category.tags"
                                :key="tag"
                                class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600">
                                {{ tag }}
                            </span>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
interface TalentCategory {
    title: string;
    description: string;
    tags: string[];
}

defineProps<{
    categories: TalentCategory[];
}>();
</script>
