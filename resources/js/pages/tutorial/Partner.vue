<template>
    <TutorialGuide
        title="Hướng dẫn cho đối tác"
        description="Các video hướng dẫn dành cho đối tác để quản lý hồ sơ, dịch vụ và đơn hàng trên Sự Kiện Tốt."
        :sections="sections"
    />
</template>

<script setup lang="ts">
import TutorialGuide from './components/TutorialGuide.vue';

const sections = [
    {
        code: '1a',
        title: 'Đối tác Cách đăng ký tài khoản nhân sự',
        youtubeId: 'YGorQgS5q5Y',
    },
    {
        code: '1b',
        title: 'Đối tác Cập nhật thông tin nhân sự',
        youtubeId: '3dwlELCBLKg',
    },
    {
        code: '2',
        title: 'Cách đăng nhập tài khoản sự kiện tốt',
        youtubeId: 'mUm3HOliEDA',
    },
    {
        code: '3',
        title: 'Đối tác Thêm dịch vụ và đăng tải video dịch vụ và thêm ảnh sau khi được duyệt',
        youtubeId: 'Pqn_Zm1v4Y0',
    },
    {
        code: '3a',
        title: 'Đối tác Thêm sửa ảnh dịch vụ sau khi dịch vụ được duyệt',
        youtubeId: 'kqWwqA6dRfY',
    },
    {
        code: '4',
        title: 'Đối tác Toàn bộ luồng đơn hàng sau khi đã được khách chốt đơn',
        youtubeId: 'K2nEZVrItd8',
    },
    {
        code: '4a',
        title: 'Đối tác Nhận đơn từ khách và đợi được khách chốt đơn',
        youtubeId: 's3iFiZ-NgAI',
    },
    {
        code: '4b',
        title: 'Đối tác Sử dụng tính năng xem lịch diễn show',
        youtubeId: 'uGgKVaF5ves',
    },
    {
        code: '4c',
        title: 'Đối tác Thêm ảnh đã đến nơi sau khi đã đến nơi tổ chức sự kiện của khách và hoàn thành đơn',
        youtubeId: '3vyNBD49snc',
    },
    {
        code: '5',
        title: 'Đối tác Nạp tiền vào ví đối tác sự kiện tốt',
        youtubeId: 'PaliGpqmVFE',
    },
];
</script>
