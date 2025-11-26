<template>
    <div class="bg-white py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Khám phá thêm
                </h2>
                <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-slate-600">
                    Ngoài quy trình cơ bản, chúng tôi còn có nhiều hướng dẫn chi tiết khác giúp bạn tận dụng tối đa nền
                    tảng.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <Link :href="route('tutorial.other')"
                        class="rounded-md bg-sky-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600">
                    Xem các hướng dẫn khác <span aria-hidden="true">→</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>
