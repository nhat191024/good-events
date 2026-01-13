<script setup lang="ts">
    import { computed } from 'vue';
    import { Link } from '@inertiajs/vue3';
    import ClientAppHeaderLayout from  '@/layouts/app/ClientHeaderLayout.vue'
    import SelectPartnerHeader from  '@/pages/booking/layout/Header.vue'
    import FormGroupLayout from '@/pages/booking/layout/FormGroup.vue'
    import Button from '@/components/ui/button/Button.vue'
    import { inject } from "vue";

    const route = inject('route') as any;

    const props = defineProps<{
        categoryName?: string;
    }>();

    const subtitle = computed(() => {
        if (props.categoryName) {
            return `Sukientot.com đã gửi yêu cầu đơn hàng đến ${props.categoryName} ở gần bạn!`;
        }

        return 'Sukientot.com đã gửi yêu cầu đơn hàng thành công!';
    });
</script>

<!-- quick booking page STEP 2 -->
<template>
    <!-- layout -->
    <ClientAppHeaderLayout :bannerBackgroundClassName="'bg-primary-200'" :showBannerBackground="false">
        <SelectPartnerHeader :title="'Tuyệt vời!'" :subtitle="subtitle" header-img-src="https://framerusercontent.com/images/IDBlVR9F6tbH9i8opwaJiutM.png?scale-down-to=512&width=1024&height=1024">
            <div class="flex items-center flex-col gap-3 w-full md:w-1/2">
                <FormGroupLayout class="mb-0">
                    <Link :class="'w-full'" :href="route('quick-booking.choose-category')">
                        <Button :variant="'outlineWhite'" :size="'lg'" :class="'w-full'">Đặt show tiếp</Button>
                    </Link>
                    <Link :class="'w-full'" :href="route('client-orders.dashboard')">
                        <Button :variant="'outlineWhite'" :size="'lg'" :class="'w-full'">Xem đơn đã đặt</Button>
                    </Link>
                </FormGroupLayout>
                <FormGroupLayout class="mb-0">
                    <Link :class="'w-full'" :href="route('home')">
                        <Button :variant="'outlineWhite'" :size="'lg'" :class="'w-full'">Về trang chủ</Button>
                    </Link>
                </FormGroupLayout>
            </div>
        </SelectPartnerHeader>
    </ClientAppHeaderLayout>
</template>
