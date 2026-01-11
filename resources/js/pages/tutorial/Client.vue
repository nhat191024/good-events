<template>
    <TutorialGuide
        title="Hướng dẫn cho khách hàng"
        description="Tổng hợp video hướng dẫn giúp khách hàng sử dụng nền tảng Sự Kiện Tốt một cách nhanh chóng và thuận tiện."
        :sections="sections"
    >
        <template #after-hero>
            <Link
                :href="route('tutorial.partner')"
                class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500"
            >
                Xem hướng dẫn cho Đối tác
                <span aria-hidden="true">→</span>
            </Link>
        </template>

        <template #after-content>
            <div class="flex justify-end">
                <Link
                    :href="route('tutorial.partner')"
                    class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-500"
                >
                    Xem hướng dẫn cho Đối tác
                    <span aria-hidden="true">→</span>
                </Link>
            </div>
        </template>
    </TutorialGuide>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import TutorialGuide from './components/TutorialGuide.vue';

const sections = [
    {
        code: '1',
        title: 'Đăng ký làm khách hàng của sự kiện tốt và đặt một đơn sự kiện mới bằng tính năng đặt đơn nhanh',
        youtubeId: '4Az5YrEGNU4',
    },
    {
        code: '1a',
        title: 'Đăng ký tài khoản khách hàng trên sự kiện tốt',
        youtubeId: 'Jzi149h-e7A',
    },
    {
        code: '1b',
        title: 'Đặt một đơn hàng mới bằng tính năng đặt đơn nhanh và kiểm tra danh sách đơn hàng đã đặt',
        youtubeId: 'R4HDQx1uGjE',
    },
    {
        code: '2',
        title: 'Toàn bộ luồng Theo dõi và hoàn thành đánh giá đơn với vai trò khách',
        youtubeId: 'RUzvFZIVmQo',
    },
    {
        code: '2a',
        title: 'Xem thông tin đối tác trước khi chốt đơn',
        youtubeId: 'qU5bTjDiDWk',
    },
    {
        code: '2b',
        title: 'Sử dụng mã giảm giá voucher trước khi chốt đối tác',
        youtubeId: 'Ow_kOyVe1Yg',
    },
    {
        code: '2c',
        title: 'Sử dụng tính năng chat để nhắn tin trên sự kiện tốt Khách',
        youtubeId: 'XkP4u0SVrDA',
    },
    {
        code: '2d',
        title: 'Sử dụng tính năng báo cáo trên sự kiện tốt - Khách',
        youtubeId: 'Bv9D8pzm18o',
    },
    {
        code: '2e',
        title: 'Đánh giá nhân sự đơn hàng sau khi hoàn thành đơn trên sự kiện tốt',
        youtubeId: 'fh956hX8bY0',
    },
    {
        code: '3',
        title: 'Trở thành đối tác Từ vai trò khách hàng trên sự kiện tốt',
        youtubeId: '4A0cPuo9vPk',
    },
];
</script>
