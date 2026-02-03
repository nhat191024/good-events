<template>

    <Head title="Tải ứng dụng Sự kiện tốt" />

    <ClientHeaderLayout>
        <div class="bg-gradient-to-br from-primary-50 via-white to-primary-100/70">
            <section class="mx-auto max-w-7xl px-4 py-12 md:py-16">
                <div class="grid gap-8 lg:grid-cols-[1.1fr,0.9fr]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-primary-600">
                            ỨNG DỤNG SỰ KIỆN TỐT
                        </p>
                        <h1 class="mt-4 text-3xl font-semibold text-gray-900 md:text-5xl">
                            Đặt show nhanh, theo dõi sự kiện ngay trên điện thoại
                        </h1>
                        <p class="mt-4 text-lg text-gray-600">
                            Sự kiện tốt giúp bạn tìm nhân sự, theo dõi trạng thái booking và nhận thông báo tức thì.
                            Dùng app PWA trên mọi thiết bị, hoặc chờ bản Android/iOS chính thức.
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <Button as-child size="lg">
                                <a href="#pwa">Cài PWA ngay</a>
                            </Button>
                            <Button as-child size="lg" variant="outline">
                                <a href="#android">Android app</a>
                            </Button>
                            <Button as-child size="lg" variant="outline">
                                <a href="#ios">iOS app</a>
                            </Button>
                        </div>
                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border border-primary-100 bg-white/80 p-3 text-sm text-gray-700">
                                <p class="font-semibold text-gray-900">Thông báo tức thì</p>
                                <p class="mt-1 text-xs text-gray-500">Cập nhật trạng thái booking theo thời gian thực.
                                </p>
                            </div>
                            <div class="rounded-lg border border-primary-100 bg-white/80 p-3 text-sm text-gray-700">
                                <p class="font-semibold text-gray-900">Tối ưu cho di động</p>
                                <p class="mt-1 text-xs text-gray-500">Giao diện gọn gàng, thao tác nhanh.</p>
                            </div>
                            <div class="rounded-lg border border-primary-100 bg-white/80 p-3 text-sm text-gray-700">
                                <p class="font-semibold text-gray-900">An toàn & bảo mật</p>
                                <p class="mt-1 text-xs text-gray-500">Đăng nhập an toàn, dữ liệu rõ ràng.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section id="android" class="mx-auto max-w-7xl px-4 py-12">
            <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr] lg:items-center">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-primary-600">ANDROID</span>
                    <h2 class="mt-3 text-2xl font-semibold text-gray-900">Ứng dụng Android</h2>
                    <p class="mt-3 text-gray-600">
                        Phiên bản Android đang hoàn thiện để tối ưu trải nghiệm đặt show và theo dõi tiến độ.
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                            Đăng nhập nhanh, lưu phiên an toàn.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                            Theo dõi lịch làm việc và nhắc lịch thông minh.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                            Giao diện tối ưu cho thiết bị Android phổ biến.
                        </li>
                    </ul>
                </div>

                <Card class="border-primary-100">
                    <CardHeader>
                        <CardTitle class="text-lg">Trạng thái</CardTitle>
                        <CardDescription>Sắp có trên Google Play.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-gray-600">
                            Trong thời gian chờ, bạn có thể dùng PWA để trải nghiệm đầy đủ tính năng.
                        </p>
                    </CardContent>
                    <CardFooter>
                        <Button disabled class="w-full cursor-not-allowed">Sắp có trên Google Play</Button>
                    </CardFooter>
                </Card>
            </div>
        </section>

        <section id="ios" class="mx-auto max-w-7xl px-4 py-12">
            <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr] lg:items-center">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-primary-600">IOS</span>
                    <h2 class="mt-3 text-2xl font-semibold text-gray-900">Ứng dụng iOS</h2>
                    <p class="mt-3 text-gray-600">
                        Phiên bản iOS sẽ tập trung vào trải nghiệm mượt mà và thông báo tức thì.
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                            Hiển thị lịch sự kiện rõ ràng theo tuần/tháng.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                            Gửi thông báo trạng thái đơn hàng tức thì.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                            Tối ưu cho iPhone và iPad.
                        </li>
                    </ul>
                </div>

                <Card class="border-primary-100">
                    <CardHeader>
                        <CardTitle class="text-lg">Trạng thái</CardTitle>
                        <CardDescription>Sắp có trên App Store.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-gray-600">
                            Bạn có thể cài PWA ngay hôm nay để dùng trên iPhone/iPad.
                        </p>
                    </CardContent>
                    <CardFooter>
                        <Button disabled class="w-full cursor-not-allowed">Sắp có trên App Store</Button>
                    </CardFooter>
                </Card>
            </div>
        </section>

        <section id="pwa" class="mx-auto max-w-7xl px-4 py-12">
            <div class="flex flex-col gap-2">
                <span class="text-xs font-semibold uppercase tracking-[0.25em] text-primary-600">PWA</span>
                <h2 class="text-2xl font-semibold text-gray-900">Cài ứng dụng PWA</h2>
                <p class="text-gray-600">
                    Dùng ngay trên trình duyệt, cài nhanh vào màn hình chính để truy cập tiện lợi.
                </p>
            </div>
            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <Card class="border-primary-100">
                    <CardHeader>
                        <CardTitle class="text-lg">Ưu điểm PWA</CardTitle>
                        <CardDescription>Đầy đủ tính năng, không tốn bộ nhớ.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                                Cài đặt nhanh trong vài thao tác.
                            </li>
                            <!-- <li class="flex items-start gap-2">
                                <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                                Nhận thông báo ngay khi có cập nhật booking.
                            </li> -->
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                                Tương thích cả Android và iOS, thậm chí PC/Laptop
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card class="border-primary-100">
                    <CardHeader>
                        <CardTitle class="text-lg">Cài trên Android</CardTitle>
                        <CardDescription>Khuyến nghị dùng Chrome.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ol class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary-600">1.</span>
                                Mở sukientot.com bằng Chrome.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary-600">2.</span>
                                Nhấn nút menu ⋮ và chọn “Cài đặt ứng dụng”.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary-600">3.</span>
                                Xác nhận để thêm biểu tượng vào màn hình chính.
                            </li>
                        </ol>
                    </CardContent>
                </Card>

                <Card class="border-primary-100">
                    <CardHeader>
                        <CardTitle class="text-lg">Cài trên iOS</CardTitle>
                        <CardDescription>Khuyến nghị dùng Safari.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ol class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary-600">1.</span>
                                Mở sukientot.com bằng Safari.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary-600">2.</span>
                                Nhấn biểu tượng Chia sẻ và chọn “Thêm vào Màn hình chính”.
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-semibold text-primary-600">3.</span>
                                Đặt tên và xác nhận để hoàn tất.
                            </li>
                        </ol>
                    </CardContent>
                </Card>
            </div>
        </section>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
</script>
