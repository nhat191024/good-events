<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import { AppSettings } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';

const route = inject('route') as any;

const page = usePage();
const settings = computed(() => page.props.app_settings as AppSettings);

const socialLinks = computed(() => {
    const s = settings.value || {};
    const links = [
        { label: 'Nhóm Zalo Đối Tác', href: s.social_zalo, icon: 'zalo' },
        { label: 'Facebook', href: s.social_facebook, icon: 'facebook' },
        { label: 'Nhóm Cộng Đồng', href: s.social_facebook_group, icon: 'facebook-group' },
        { label: 'YouTube', href: s.social_youtube, icon: 'youtube' },
        { label: 'TikTok', href: s.social_tiktok, icon: 'tiktok' },
    ];
    return links.filter((l) => !!l.href);
});

const primaryLinks = [
    { label: 'Câu hỏi thường gặp', routeName: 'static.faq' },
    { label: 'Hướng dẫn', routeName: 'tutorial.index' },
    { label: 'Về chúng tôi', routeName: 'about.index' },
    { label: 'Chương trình khuyến mãi', href: '#' },
    { label: 'Phiên bản phát hành', href: '#' },
];

const policyLinks = [
    { label: 'Điều khoản và Điều kiện', routeName: 'static.terms' },
    { label: 'Chính sách bảo mật', routeName: 'static.privacy' },
    { label: 'Chính sách vận chuyển và phương thức thanh toán', routeName: 'static.shipping' },
    { label: 'Liên hệ', routeName: 'contact.index' },
    // { label: 'White Paper', href: '/VT-White-Paper.pdf' },
    { label: 'Tuyển dụng', routeName: 'contact.index' },
];

const downloadButtons = [
    { label: 'PWA (Cài App 1 chạm)', routeName: 'static.download-app' },
    { label: 'Google Play', href: '#' },
    { label: 'App Store', href: '#' },
];

const address = '36 Nguyễn Thiện Thuật, phường Bình Thạnh, quận Bình Thạnh, TP. Hồ Chí Minh';
const taxCode = 'Mã số thuế: 0319314603';
</script>

<template>
    <footer class="bg-primary-500 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
            <div class="grid gap-3 md:gap-10 lg:grid-cols-3 lg:items-start">
                <div class="space-y-6">
                    <Link :href="route('home')" class="inline-block">
                        <img :src="getImg(`${settings.app_logo}`)" alt="Sự Kiện Tốt"
                            class="rounded-full h-16 w-auto object-contain" loading="lazy" />
                    </Link>

                    <div class="space-y-3">
                        <p class="text-lg font-semibold">Liên hệ</p>
                        <div class="flex flex-wrap gap-3">
                            <a v-for="social in socialLinks" :key="social.label" :href="social.href" target="_blank"
                                rel="noreferrer"
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-[#0D308C] transition hover:opacity-90"
                                :aria-label="social.label">
                                <svg v-if="social.icon === 'facebook'" xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M13.5 9.5V7.75c0-.8.4-1.5 1.7-1.5h1.3V3h-2.2C11.6 3 10 4.8 10 7.4V9.5H8v2.9h2V21h3.5v-8.6h2.3l.2-2.9H13.5Z" />
                                </svg>
                                <svg v-else-if="social.icon === 'facebook-group'" xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <svg v-else-if="social.icon === 'tiktok'" xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M19.2 8.1c-1.1-.4-2-1.2-2.5-2.3h-.2v9.5c0 2.9-2.3 5.2-5.2 5.2S6 18.2 6 15.3c0-2.9 2.3-5.2 5.2-5.2.3 0 .7 0 1 .1v3A2.2 2.2 0 0 0 9 15.3c0 1.2 1 2.2 2.3 2.2 1.2 0 2.2-1 2.2-2.2V3h3.5c.2 1.2 1 2.2 2.2 2.7v2.4Z" />
                                </svg>
                                <svg v-else-if="social.icon === 'youtube'" xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M22 7.3c-.2-1-.9-1.8-1.9-2C18.1 5 12 5 12 5s-6.1 0-8.1.3c-1 .2-1.7 1-1.9 2C1.7 9 1.7 12 1.7 12s0 3 .3 4.7c.2 1 .9 1.8 1.9 2C5.9 19 12 19 12 19s6.1 0 8.1-.3c1-.2 1.7-1 1.9-2 .3-1.7.3-4.7.3-4.7s0-3-.3-4.7ZM10 15.2V8.8l5.3 3.2-5.3 3.2Z" />
                                </svg>
                                <svg v-else-if="social.icon === 'zalo'" xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M18.5 2H5.5A3.5 3.5 0 0 0 2 5.5v13A3.5 3.5 0 0 0 5.5 22h13a3.5 3.5 0 0 0 3.5-3.5v-13A3.5 3.5 0 0 0 18.5 2ZM8.3 15.4H5.7l2.8-4H5.9V9h4.1v2.1l-1.7 2.5Zm2.4 0V9h2v6.4Zm6 0-2.4-3v3h-2V9h1.9l2.3 2.8V9h2v6.4Z" />
                                </svg>
                            </a>
                        </div>

                        <div class="space-y-3 text-sm leading-7">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path
                                        d="M6.7 3.1 9 2c.8-.4 1.8 0 2 1l.5 2.2c.2.8 0 1.5-.5 2L9.7 8.5c1 1.9 2.6 3.5 4.5 4.5l1.2-1.3c.5-.5 1.2-.7 2-.5l2.2.5c1 .2 1.4 1.2 1 2l-1.1 2.3c-.4.8-1.2 1.3-2.1 1.2-3-.4-6-2-8.4-4.4-2.4-2.4-4-5.4-4.4-8.4-.2-.9.3-1.7 1.1-2.1Z" />
                                </svg>
                                <span>{{ settings.contact_hotline ?? '0393 719 095' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path
                                        d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Zm0 2v.5l8 5 8-5V6H4Zm16 12V9.2l-7.4 4.6c-.4.3-.9.3-1.2 0L4 9.2V18h16Z" />
                                </svg>
                                <span>{{ settings.contact_email ?? 'sukientot9095@gmail.com' }}</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path
                                        d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z" />
                                </svg>
                                <span>{{ address }}</span>
                            </div>
                            <p class="text-sm leading-7">{{ taxCode }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <p class="text-lg font-semibold">Tải ứng dụng:</p>
                    <div class="flex flex-wrap gap-3">
                        <component :is="button.routeName ? Link : 'a'" v-for="button in downloadButtons"
                            :key="button.label" :href="button.routeName ? route(button.routeName) : button.href"
                            class="rounded-xl bg-white/15 px-4 py-3 text-sm font-semibold backdrop-blur transition hover:bg-white/25">
                            {{ button.label }}
                        </component>
                    </div>
                </div>

                <div class="grid gap-8 sm:grid-cols-2">
                    <div class="space-y-3">
                        <div v-for="link in primaryLinks" :key="link.label">
                            <component :is="link.routeName ? Link : 'a'"
                                :href="link.routeName ? route(link.routeName) : link.href"
                                class="text-sm font-semibold hover:underline">
                                {{ link.label }}
                            </component>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div v-for="link in policyLinks" :key="link.label">
                            <component :is="link.routeName ? Link : 'a'"
                                :href="link.routeName ? route(link.routeName) : link.href"
                                class="text-sm font-semibold hover:underline">
                                {{ link.label }}
                            </component>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="mt-10 flex flex-col items-center justify-between gap-4 rounded-2xl bg-white/10 px-5 py-4 backdrop-blur md:flex-row">
                <p class="text-sm text-blue-100">© 2025. All rights reserved.</p>
                <div class="flex flex-col items-center gap-3 md:flex-row md:gap-6">
                    <p class="text-sm font-medium text-white">CÔNG TY TNHH CÔNG NGHỆ SỰ KIỆN TỐT</p>
                    <!-- <img src="/images/gov.webp" alt="Đã thông báo Bộ Công Thương" class="rounded h-12 w-auto object-contain" loading="lazy" /> -->
                </div>
            </div>
        </div>
    </footer>
</template>
