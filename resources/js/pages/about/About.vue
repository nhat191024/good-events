<template>
    <Head title="Về chúng tôi" />

    <ClientHeaderLayout>
        <div class="bg-primary-50 w-full">
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(0)"
            >
                <AboutHero :content="heroContent" :highlights="heroHighlights" :service-pills="servicePills" :gallery="heroGallery" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(1)"
            >
                <AboutRoles :roles="roles" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(2)"
            >
                <AboutTalentShowcase :categories="talentCategories" />
            </motion.section>
        </div>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { Head, usePage } from '@inertiajs/vue3';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

import AboutHero from './components/AboutHero.vue';
import AboutRoles from './components/AboutRoles.vue';
import AboutTalentShowcase from './components/AboutTalentShowcase.vue';
import { computed, watch } from 'vue';
import { tutorialQuickLinks } from '@/lib/tutorial-links';
import { useTutorialHelper } from '@/lib/tutorial-helper';

const sectionMotion = {
    initial: { opacity: 0, y: 40 },
    visible: { opacity: 1, y: 0 },
    viewport: { once: true, amount: 0.2 },
} as const;

const getSectionTransition = (index: number) => ({
    duration: 0.6,
    ease: 'easeOut',
    delay: Math.min(index * 0.08, 0.4),
});

const heroContent = {
    eyebrow: 'Sàn nhân sự sự kiện',
    title: 'Đặt chú hề, MC, ảo thuật trong 30 giây',
    description: 'Một nơi duy nhất để khách hàng đặt lịch và nhà cung cấp nhận show nhanh, minh bạch.',
    subheading: 'Chủ đề hay gặp',
};

const servicePills = [
    { label: 'Sinh nhật thiếu nhi' },
    { label: 'Khai trương' },
    { label: 'Lễ hội trường học' },
    { label: 'Roadshow thương hiệu' },
];

const heroHighlights = [
    {
        kicker: 'Đặt lịch tức thì',
        title: '30 giây có ngay nhân sự',
        description: 'Bước vào – chọn nhân sự – chốt ngày.',
    },
    {
        kicker: 'Sàn trung gian',
        title: 'Kết nối minh bạch',
        description: 'Hợp đồng rõ ràng, thanh toán an toàn cho cả hai phía.',
    },
];

const heroGallery = [
    {
        label: 'Khách hàng',
        caption: 'Chọn lịch – chọn concept – xem giá ngay',
        image: 'https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=1200&q=80',
    },
    {
        label: 'Đối tác',
        caption: 'Nhận show phù hợp năng lực, thanh toán an toàn',
        image: 'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=1200&q=80',
    },
    {
        label: 'Kho tiết mục',
        caption: 'Mascot, ảo thuật, workshop… đủ sẵn',
        image: 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80',
    },
];

const roles = [
    {
        slug: 'client',
        badge: 'Khách hàng',
        title: 'Đặt dịch vụ sự kiện siêu nhanh',
        summary: 'Tất cả nhân sự – giá – lịch trống hiển thị ngay, chọn xong là đặt.',
        points: ['Bảng giá công khai và đánh giá thật', 'Theo dõi tiến độ, đổi lịch chỉ với một chạm'],
        image: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1500&q=80',
        emphasis: true,
    },
    {
        slug: 'partner',
        badge: 'Đối tác / Nhân sự',
        title: 'Nhận show đều, có người hậu cần',
        summary: 'Hồ sơ duyệt nhanh, đội ngũ admin đứng giữa bảo vệ quyền lợi.',
        points: ['Lọc show theo khu vực & kỹ năng', 'Thanh toán rõ ràng, hỗ trợ khi có sự cố'],
        image: 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?auto=format&fit=crop&w=1500&q=80',
    },
];

const talentCategories = [
    {
        title: 'Chú hề & hoạt náo',
        description: 'Vui nhộn, thân thiện, hợp cho sinh nhật và khai trương.',
        tags: ['Vặn bóng', 'Chú hề đa năng', 'Hoạt náo thiếu nhi'],
        cover: 'https://images.unsplash.com/photo-1518895949257-7621c3c786d4?auto=format&fit=crop&w=1200&q=80',
    },
    {
        title: 'MC & mascot',
        description: 'MC linh hoạt concept, mascot đáng nhớ cho mọi độ tuổi.',
        tags: ['MC vest', 'MC chú hề', 'Mascot gấu/robot'],
        cover: 'https://images.unsplash.com/photo-1464375117522-1311d6a5b81f?auto=format&fit=crop&w=1200&q=80',
    },
    {
        title: 'Ảo thuật & xiếc',
        description: 'Từ cận cảnh tới sân khấu, khiến sân khấu bùng nổ.',
        tags: ['Ảo thuật gia', 'Xiếc thăng bằng', 'Robot LED'],
        cover: 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1200&q=80',
    },
    {
        title: 'Nghệ nhân truyền thống',
        description: 'Giữ trọn màu sắc văn hoá cho sự kiện.',
        tags: ['Tò he', 'Lá dừa', 'Ông đồ'],
        cover: 'https://images.unsplash.com/photo-1527933053326-89d1746b76c2?auto=format&fit=crop&w=1200&q=80',
    },
];

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const { addTutorialRoutes } = useTutorialHelper();

watch(
    user,
    (value) => {
        setTutorialRoutesBasedOnAuth(value);
        // clearTutorialRoutes();
    },
    { immediate: true }
);

function setTutorialRoutesBasedOnAuth(value: any) {
    if (!value) {
        addTutorialRoutes([tutorialQuickLinks.clientRegister, tutorialQuickLinks.partnerRegister, tutorialQuickLinks.clientRegisterAndFastBooking]);
    }
    else {
        addTutorialRoutes([tutorialQuickLinks.clientQuickOrder]);
    }
}
</script>
