<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

</script>

<template>
    <!-- Accent line (chỉ 1 line đỏ mỏng) -->
    <div class="w-full h-[2px] bg-[#ED3B50]"></div>

    <!-- App Download strip -->
    <div class="bg-white">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <h3 class="text-black font-semibold text-lg">Tải ứng dụng Sukientot</h3>
            <div class="flex items-center gap-3">
                <!-- App Store Button -->
                <a href="#" class="block" aria-label="Tải trên App Store">
                    <img src="/images/download_on_appstore.png" alt="Download on the App Store"
                        class="h-[100] w-[135px] object-contain align-middle" loading="lazy" />
                </a>
                <!-- Google Play Button -->
                <a href="#" class="block" aria-label="Tải trên Google Play">
                    <img src="/images/get_on_googleplay.png" alt="Get it on Google Play"
                        class="h-[200] w-[170px] object-contain align-middle" loading="lazy" />
                </a>
            </div>
        </div>
    </div>

    <!-- Main Footer (không có line separator) -->
    <footer class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-6 gap-8">
                <!-- Logo -->
                <Link :href="route('home')" class="col-span-2 md:col-span-1">
                    <img src="/images/logo.png" alt="Sukientot" class="h-26 w-26 rounded-full object-contain" />
                </Link>

                <!-- Sự kiện -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Sự kiện</h4>
                    <ul class="space-y-2">
                        <li><Link href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Chú hề</Link></li>
                        <li><Link href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Ảo thuật gia</Link>
                        </li>
                        <li><Link href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Mascot</Link></li>
                    </ul>
                </div>

                <!-- Vật tư -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Vật tư</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Loa đài</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Ánh sáng</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Cổng</a></li>
                    </ul>
                </div>

                <!-- Tài liệu -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Tài liệu</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Banner</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Thiết kế</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Ảnh nền</a></li>
                    </ul>
                </div>

                <!-- Khách sạn -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-4">Khách sạn</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Blog</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Tìm nhanh</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-[#ED3B50] transition-colors">Giá tốt</a></li>
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
                <div class="text-xs text-gray-600 space-y-1 max-w-4xl">
                    <p>
                        <strong>CÔNG TY TNHH SUKIENTOT</strong> - Người đại diện theo pháp luật: Nguyễn Sự Kiện; GPĐKKD:
                        0124356787 do Sở KH &amp; ĐT TP.HCM cấp ngày 11/01/2025;
                    </p>
                    <p>
                        GPMXH: 185/GP-BTTTT do Bộ Thông tin và Truyền thông cấp ngày 08/07/2025.
                    </p>
                    <p>
                        Email: trogiup@sukientot.vn - Tổng đài CSKH: 1900-100-69 (5000đ/phút)
                    </p>
                    <p>
                        Chịu trách nhiệm nội dung: Trần Kiên Trí. Chính sách sử dụng. Địa chỉ: Tầng 69, Tòa nhà OIA, Số
                        69 đường Trần Tôn, Phường Mỹ Tân, Thành phố Hồ Chí Minh, Việt Nam.
                    </p>
                </div>

                <!-- Certification Badge -->
                <div class="mt-4 lg:mt-0 flex-shrink-0">
                    <img src="/images/gov.png" alt="Đã đăng ký Bộ Công Thương" class="h-20 w-[150px] object-contain"
                        loading="lazy" />
                </div>
            </div>
        </div>
    </footer>
</template>
