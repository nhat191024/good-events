<template>

    <Head title="Hướng dẫn khác" />
    <ClientAppHeaderLayout>
        <TutorialHero title="Các hướng dẫn khác"
            description="Khám phá thêm các tính năng thú vị khác của Good Event để tận dụng tối đa nền tảng của chúng tôi." />

        <div class="bg-white">
            <TutorialStep v-for="(step, index) in steps" :key="index" :step-number="index + 1" :title="step.title"
                :description="step.description" :image="step.image" :is-reversed="index % 2 !== 0" />
        </div>
    </ClientAppHeaderLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import TutorialHero from './components/TutorialHero.vue';
import TutorialStep from './components/TutorialStep.vue';

const steps = [
    {
        title: 'Tính năng 1',
        description: 'Mô tả chi tiết về tính năng 1 và cách sử dụng nó hiệu quả.',
        image: 'https://placehold.co/800x600?text=Tinh+Nang+1',
    },
    {
        title: 'Tính năng 2',
        description: 'Mô tả chi tiết về tính năng 2 và lợi ích nó mang lại.',
        image: 'https://placehold.co/800x600?text=Tinh+Nang+2',
    },
    {
        title: 'Tính năng 3',
        description: 'Hướng dẫn sử dụng tính năng 3 để tối ưu hóa trải nghiệm của bạn.',
        image: 'https://placehold.co/800x600?text=Tinh+Nang+3',
    },
];
</script>
