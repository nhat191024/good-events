<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import { AppSettings } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { computed } from 'vue';

const page = usePage()

const settings = computed(() => page.props.app_settings as AppSettings)

const accentMotion = {
    initial: { scaleX: 0, opacity: 0 },
    visible: { scaleX: 1, opacity: 1 },
    transition: { duration: 0.5, ease: 'easeOut' },
    viewport: { once: true, amount: 0.2 },
} as const;

const downloadMotion = {
    initial: { opacity: 0.5, y: 24 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: 'easeOut' },
    viewport: { once: true, amount: 0.25 },
} as const;

const downloadButtonInteractions = {
    hover: {
        scale: 1.04,
        transition: { type: 'spring', duration: 0.5, bounce: 0.32 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.45, bounce: 0.25 },
    },
} as const;

const footerContentMotion = {
    initial: { opacity: 0.5, y: 30 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.65, ease: 'easeOut' },
    viewport: { once: true, amount: 0.25 },
} as const;

const infoMotion = {
    initial: { opacity: 0.5, y: 20 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: 'easeOut', delay: 0.1 },
    viewport: { once: true, amount: 0.2 },
} as const;

const badgeMotion = {
    initial: { opacity: 0.5, scale: 0.9 },
    visible: { opacity: 1, scale: 1 },
    transition: { duration: 0.45, ease: 'easeOut' },
    viewport: { once: true, amount: 0.2 },
} as const;
</script>

<template>
    <!-- Accent line (chỉ 1 line đỏ mỏng) -->
    <motion.div class="w-full h-[2px] bg-[#ED3B50]" :initial="accentMotion.initial"
        :while-in-view="accentMotion.visible" :viewport="accentMotion.viewport" :transition="accentMotion.transition">
    </motion.div>

    <!-- App Download strip -->
    <motion.div class="bg-white" :initial="downloadMotion.initial" :while-in-view="downloadMotion.visible"
        :viewport="downloadMotion.viewport" :transition="downloadMotion.transition">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <h3 class="text-black font-semibold text-lg">Tải ứng dụng Sukientot</h3>
            <div class="flex items-center gap-3">
                <!-- App Store Button -->
                <motion.a href="#" class="block" aria-label="Tải trên App Store"
                    :while-hover="downloadButtonInteractions.hover" :while-tap="downloadButtonInteractions.tap">
                    <img src="/images/download_on_appstore.png" alt="Download on the App Store"
                        class="h-[100] w-[135px] object-contain align-middle" loading="lazy" />
                </motion.a>
                <!-- Google Play Button -->
                <motion.a href="#" class="block" aria-label="Tải trên Google Play"
                    :while-hover="downloadButtonInteractions.hover" :while-tap="downloadButtonInteractions.tap">
                    <img src="/images/get_on_googleplay.png" alt="Get it on Google Play"
                        class="h-[100px] w-[170px] object-contain align-middle" loading="lazy" />
                </motion.a>
            </div>
        </div>
    </motion.div>

    <!-- Main Footer (không có line separator) -->
    <footer class="bg-white md:py-12 py-2">
        <motion.div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" :initial="footerContentMotion.initial"
            :while-in-view="footerContentMotion.visible" :viewport="footerContentMotion.viewport"
            :transition="footerContentMotion.transition">
            <div class="grid grid-cols-2 md:grid-cols-6 gap-8">
                <!-- Logo -->
                <Link :href="route('home')" class="col-span-2 md:col-span-1">
                <img :src="getImg(`/${settings.app_logo}`)" alt="Sukientot"
                    class="h-26 w-26 rounded-full object-contain" />
                </Link>

                <!-- Sự kiện -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Sự kiện</h4>
                    <ul class="space-y-2">
                        <li>
                            <Link href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Chú hề</Link>
                        </li>
                        <li>
                            <Link href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Ảo thuật gia
                            </Link>
                        </li>
                        <li>
                            <Link href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Mascot</Link>
                        </li>
                    </ul>
                </div>

                <!-- Thiết bị -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Thiết bị</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Loa đài</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Ánh sáng</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Cổng</a></li>
                    </ul>
                </div>

                <!-- Thiết kế -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Thiết kế</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Banner</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Thiết kế</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Ảnh nền</a></li>
                    </ul>
                </div>

                <!-- Blog -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Blog</h4>
                    <ul class="space-y-2">
                        <li>
                            <Link :href="route('blog.discover')"
                                class="text-gray-600 hover:text-[#ED3B50] transition-colors">Địa điểm</Link>
                        </li>
                        <li>
                            <Link :href="route('blog.guides.discover')"
                                class="text-gray-600 hover:text-[#ED3B50] transition-colors">Hướng dẫn sự kiện</Link>
                        </li>
                        <li>
                            <Link :href="route('blog.knowledge.discover')"
                                class="text-gray-600 hover:text-[#ED3B50] transition-colors">Kiến thức nghề</Link>
                        </li>
                    </ul>
                </div>

                <!-- Tuyển dụng -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Tuyển dụng</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Đối tác</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Chi tiết</a></li>
                    </ul>
                </div>
            </div>

            <!-- Company Info -->
            <div class="mt-12 flex flex-col lg:flex-row justify-between items-start lg:items-center">
                <motion.div class="text-xs text-gray-600 space-y-1 max-w-4xl" :initial="infoMotion.initial"
                    :while-in-view="infoMotion.visible" :viewport="infoMotion.viewport"
                    :transition="infoMotion.transition">
                    <p>
                        <strong>CÔNG TY TNHH SUKIENTOT</strong> - Người đại diện theo pháp luật: Nguyễn Sự Kiện; GPĐKKD:
                        0124356787 do Sở KH &amp; ĐT TP.HCM cấp ngày 11/01/2025;
                    </p>
                    <p>
                        GPMXH: 185/GP-BTTTT do Bộ Thông tin và Truyền thông cấp ngày 08/07/2025.
                    </p>
                    <p>
                        Email: {{ settings.contact_email }} - Tổng đài CSKH: {{ settings.contact_hotline }} (5000đ/phút)
                    </p>
                    <p>
                        Chịu trách nhiệm nội dung: Trần Kiên Trí. Chính sách sử dụng. Địa chỉ: Tầng 69, Tòa nhà OIA, Số
                        69 đường Trần Tôn, Phường Mỹ Tân, Thành phố Hồ Chí Minh, Việt Nam.
                    </p>
                </motion.div>

                <!-- Certification Badge -->
                <motion.div class="mt-4 lg:mt-0 flex-shrink-0" :initial="badgeMotion.initial"
                    :while-in-view="badgeMotion.visible" :viewport="badgeMotion.viewport"
                    :transition="badgeMotion.transition">
                    <img src="/images/gov.webp" alt="Đã đăng ký Bộ Công Thương" class="h-20 w-[150px] object-contain"
                        loading="lazy" />
                </motion.div>
            </div>
        </motion.div>
    </footer>
</template>
