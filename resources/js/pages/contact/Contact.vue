<template>
    <Head title="Liên hệ" />

    <ClientHeaderLayout>
        <div class="bg-primary-50">
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(0)"
            >
                <ContactHero />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(1)"
            >
                <ContactOptions :channels="contactChannels" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(2)"
            >
                <ContactForm :topics="formTopics" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(3)"
            >
                <ContactFAQ :faqs="faqs" />
            </motion.section>
        </div>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { Head } from '@inertiajs/vue3';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

import ContactFAQ from './components/ContactFAQ.vue';
import ContactForm from './components/ContactForm.vue';
import ContactHero from './components/ContactHero.vue';
import ContactOptions from './components/ContactOptions.vue';

interface ContactChannel {
    title: string;
    description: string;
    detail: string;
    hint: string;
}

interface ContactFAQ {
    question: string;
    answer: string;
}

const sectionMotion = {
    initial: { opacity: 0, y: 40 },
    visible: { opacity: 1, y: 0 },
    viewport: { once: true, amount: 0.2 },
} as const;

const getSectionTransition = (index: number) => ({
    duration: 0.6,
    ease: 'easeOut',
    delay: Math.min(index * 0.08, 0.32),
});

const contactChannels: ContactChannel[] = [
    {
        title: 'Tư vấn sự kiện',
        description: 'Đội ngũ chuyên gia đồng hành từ khâu lập kế hoạch đến triển khai.',
        detail: 'hotline: 1900 636 902 (7:00 - 22:00)',
        hint: 'Ưu tiên phản hồi trong 15 phút',
    },
    {
        title: 'Hợp tác đối tác',
        description: 'Kết nối để trở thành nhà cung cấp dịch vụ chính thức của Sukientot.',
        detail: 'partners@sukientot.vn',
        hint: 'Chia sẻ hồ sơ năng lực để được duyệt nhanh.',
    },
    {
        title: 'Chăm sóc khách hàng',
        description: 'Giải đáp thắc mắc sử dụng nền tảng và hỗ trợ kỹ thuật.',
        detail: 'support@sukientot.vn',
        hint: 'Có mặt 24/7 thông qua email và chat.',
    },
];

const formTopics = [
    { value: 'demo', label: 'Đặt lịch demo nền tảng' },
    { value: 'partnership', label: 'Đề xuất hợp tác/đối tác' },
    { value: 'support', label: 'Cần hỗ trợ kỹ thuật' },
    { value: 'other', label: 'Khác' },
];

const faqs: ContactFAQ[] = [
    {
        question: 'Mất bao lâu để nhận phản hồi?',
        answer: 'Trong giờ làm việc, chúng tôi phản hồi qua email hoặc điện thoại trong vòng 2 giờ. Yêu cầu gấp hãy gọi trực tiếp hotline.',
    },
    {
        question: 'Sukientot hỗ trợ những khu vực nào?',
        answer: 'Hiện chúng tôi có đối tác ở 18 tỉnh/thành, ưu tiên Hà Nội, TP.HCM, Đà Nẵng và các khu vực lân cận.',
    },
    {
        question: 'Có thể đặt lịch gặp trực tiếp không?',
        answer: 'Hoàn toàn có. Hãy chọn chủ đề "Đặt lịch demo" và ghi rõ thời gian mong muốn, đội ngũ sẽ xác nhận lại.',
    },
];
</script>
