<template>
    <form class="space-y-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm" @submit.prevent="emit('submit')">
        <header class="space-y-1">
            <h1 class="text-2xl font-semibold text-gray-900">Thông tin người mua</h1>
            <p class="text-sm text-gray-500">Vui lòng điền đầy đủ thông tin để Sukientot liên hệ và gửi tài liệu.</p>
        </header>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="flex flex-col gap-2">
                <label for="buyer-name" class="text-sm font-medium text-gray-700">Họ và tên</label>
                <Input id="buyer-name" v-model="props.form.name" type="text" placeholder="Nguyễn Văn A" autocomplete="name" />
                <p v-if="props.form.errors.name" class="text-xs text-red-500">{{ props.form.errors.name }}</p>
            </div>
            <div class="flex flex-col gap-2">
                <label for="buyer-phone" class="text-sm font-medium text-gray-700">Số điện thoại</label>
                <Input id="buyer-phone" v-model="props.form.phone" type="tel" placeholder="0901 234 567" autocomplete="tel" />
                <p v-if="props.form.errors.phone" class="text-xs text-red-500">{{ props.form.errors.phone }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="flex flex-col gap-2">
                <label for="buyer-email" class="text-sm font-medium text-gray-700">Email</label>
                <Input id="buyer-email" v-model="props.form.email" type="email" placeholder="ban@doanhnghiep.vn" autocomplete="email" />
                <p v-if="props.form.errors.email" class="text-xs text-red-500">{{ props.form.errors.email }}</p>
            </div>
            <div class="flex flex-col gap-2">
                <label for="buyer-company" class="text-sm font-medium text-gray-700">Đơn vị (không bắt buộc)</label>
                <Input id="buyer-company" v-model="props.form.company" type="text" placeholder="Công ty TNHH Sự Kiện" autocomplete="organization" />
                <p v-if="props.form.errors.company" class="text-xs text-red-500">{{ props.form.errors.company }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="flex flex-col gap-2">
                <label for="buyer-tax" class="text-sm font-medium text-gray-700">Mã số thuế (nếu cần xuất hoá đơn)</label>
                <Input id="buyer-tax" v-model="props.form.tax_code" type="text" placeholder="0312345678" />
                <p v-if="props.form.errors.tax_code" class="text-xs text-red-500">{{ props.form.errors.tax_code }}</p>
            </div>
            <div class="flex flex-col gap-2">
                <label for="buyer-method" class="text-sm font-medium text-gray-700">Hình thức thanh toán</label>
                <select
                    id="buyer-method"
                    v-model="props.form.payment_method"
                    class="h-11 rounded-lg border border-gray-200 px-3 text-sm text-gray-700 shadow-xs outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-200"
                >
                    <option v-for="method in props.paymentMethodOptions" :key="method.code" :value="method.code">
                        {{ method.name }}
                    </option>
                </select>
                <p v-if="props.form.errors.payment_method" class="text-xs text-red-500">{{ props.form.errors.payment_method }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <label for="buyer-note" class="text-sm font-medium text-gray-700">Ghi chú thêm</label>
            <textarea
                id="buyer-note"
                v-model="props.form.note"
                rows="4"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 shadow-xs outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-200"
                placeholder="Ví dụ: cần chỉnh sửa màu sắc theo nhận diện thương hiệu."
            ></textarea>
            <p v-if="props.form.errors.note" class="text-xs text-red-500">{{ props.form.errors.note }}</p>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl bg-primary-50/60 p-4 text-sm text-primary-800">
            <strong class="text-primary-900">Quy trình thực hiện</strong>
            <ol class="list-decimal space-y-1 pl-4">
                <li>Đội ngũ Sukientot liên hệ xác nhận trong vòng 24 giờ.</li>
                <li>Thanh toán theo phương thức bạn lựa chọn.</li>
                <li>Nhận toàn bộ file thiết kế và hướng dẫn triển khai qua email.</li>
            </ol>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
            <Button type="button" variant="outline" class="text-sm font-semibold text-primary-700" @click="emit('back')">
                Quay lại
            </Button>
            <Button type="submit" class="text-sm font-semibold text-white" :disabled="props.form.processing">
                Hoàn tất đăng ký
            </Button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import type { PaymentMethod, PurchaseForm } from '@/pages/asset/purchase/types';

interface PurchaseFormProps {
    form: PurchaseForm;
    paymentMethodOptions: PaymentMethod[];
}

const props = defineProps<PurchaseFormProps>();
const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'back'): void;
}>();
</script>
