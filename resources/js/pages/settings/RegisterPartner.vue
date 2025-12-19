<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref, reactive, watch } from 'vue';
import axios from 'axios';
import { LoaderCircle } from 'lucide-vue-next';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SelectBox from '@/components/Select.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import { type Province, type Ward, type WardTypeSelectBox } from '@/types/database';
import { route } from '@/utils/ziggy';

interface Props {
    provinces: Province[];
    isPartner?: boolean;
    partnerDashboardUrl?: string;
}

const props = defineProps<Props>();

const provinceList = [
    {
        name: 'Chọn tỉnh thành',
        children: (props.provinces ?? []).map((p) => ({ name: p.name, value: String(p.id) })),
    },
];

const showForm = ref(false);

const form = useForm({
    identity_card_number: '',
    ward_id: null as string | null,
});

const location = reactive({
    provinceId: null as string | null,
});

const wardList = ref<WardTypeSelectBox[]>([]);
const loadingWards = ref(false);
const wardsError = ref('');
const lastProvinceProcessed = ref<string | null>(null);

watch(
    () => location.provinceId,
    async (provinceId) => {
        // clear khi bỏ chọn
        if (!provinceId) {
            wardList.value = [];
            lastProvinceProcessed.value = null;
            form.ward_id = null;
            return;
        }

        // tránh gọi trùng
        if (lastProvinceProcessed.value === provinceId) return;
        lastProvinceProcessed.value = provinceId;

        // reset UI
        form.ward_id = null;
        wardList.value = [];
        wardsError.value = '';
        loadingWards.value = true;

        try {
            const response = await axios.get<Ward[]>(`/api/locations/${provinceId}/wards`, {
                headers: { Accept: 'application/json' },
            });
            const data = response.data;
            wardList.value = data.map((w) => ({ name: w.name, value: String(w.id) }));
        } catch (err) {
            wardsError.value = 'không tải được danh sách phường/xã';
            console.error(err);
        } finally {
            loadingWards.value = false;
        }
    }
);

const canSubmit = computed(() => {
    return (
        !!form.identity_card_number &&
        !!form.ward_id &&
        !loadingWards.value
    );
});

function submit() {
    if (!form.ward_id) {
        form.setError('ward_id', 'hãy chọn phường/xã');
        return;
    }

    form.post(route('partner.register.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by backend/Inertia location
        },
    });
}
</script>

<template>
    <ClientHeaderLayout :show-footer="false">
        <AppLayout :breadcrumbs="[{ title: 'Đăng ký làm nhân sự', href: route('partner.register.create') }]">

            <Head title="Đăng ký làm nhân sự" />

            <SettingsLayout>
                <div class="flex flex-col space-y-6">
                    <HeadingSmall title="Đăng ký làm nhân sự" description="Trở thành đối tác của chúng tôi" />

                    <div
                        class="rounded-2xl border border-neutral-200/60 bg-white/90 shadow-sm backdrop-blur p-6 lg:p-8">

                        <!-- Case 1: Already a Partner -->
                        <div v-if="props.isPartner"
                            class="flex flex-col items-center justify-center py-10 space-y-6 text-center">
                            <h2 class="text-xl font-semibold text-neutral-800">Bạn đã là đối tác</h2>
                            <p class="max-w-md text-neutral-500">
                                Tài khoản của bạn đã được đăng ký vai trò nhân sự. Bạn có thể truy cập trang quản trị
                                thông tin nhân sự để cập nhật hồ sơ.
                            </p>
                            <a :href="props.partnerDashboardUrl"
                                class="inline-flex items-center justify-center px-8 py-2 text-sm font-bold text-white transition-colors rounded-md bg-primary hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50">
                                Truy cập trang quản trị
                            </a>
                        </div>

                        <!-- Case 2: Not a Partner, Form not visible yet -->
                        <div v-else-if="!showForm"
                            class="flex flex-col items-center justify-center py-10 space-y-6 text-center">
                            <h2 class="text-xl font-semibold text-neutral-800">Bạn có muốn đăng ký vai trò nhân sự của
                                chúng tôi không?</h2>
                            <p class="max-w-md text-neutral-500">
                                Khi trở thành nhân sự, bạn có thể nhận các công việc từ hệ thống và gia tăng thu nhập.
                                Vui lòng đảm bảo bạn có đầy đủ giấy tờ tùy thân hợp lệ.
                            </p>
                            <Button @click="showForm = true" size="lg" class="px-8 font-bold text-white">
                                Đăng ký ngay
                            </Button>
                        </div>

                        <div v-else class="max-w-xl mx-auto space-y-6">
                            <div class="space-y-1 text-center sm:text-left">
                                <h2 class="text-lg font-semibold text-neutral-900">Thông tin đăng ký</h2>
                                <p class="text-sm text-neutral-500">Vui lòng điền các thông tin còn thiếu bên dưới</p>
                            </div>

                            <form @submit.prevent="submit" class="space-y-6">

                                <div class="grid gap-2">
                                    <Label for="identity_card_number">Số CCCD / CMND</Label>
                                    <Input id="identity_card_number" type="text" required autofocus
                                        v-model="form.identity_card_number" placeholder="Nhập số CCCD/CMND" />
                                    <InputError :message="form.errors.identity_card_number" />
                                </div>

                                <div class="grid gap-2">
                                    <Label>Địa chỉ hoạt động</Label>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div class="grid gap-2">
                                            <Label for="province" class="text-xs text-muted-foreground">Tỉnh / Thành
                                                phố</Label>
                                            <SelectBox id="province" v-model="location.provinceId"
                                                :options="provinceList" placeholder="Chọn Tỉnh/Thành..." />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="ward" class="text-xs text-muted-foreground">Phường / Xã</Label>
                                            <SelectBox :is-enable="location.provinceId !== null" id="ward"
                                                v-model="form.ward_id" :options="wardList" :loading="loadingWards"
                                                placeholder="Chọn Phường/Xã..." />
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.ward_id || wardsError" />
                                </div>

                                <div class="flex items-center justify-end gap-4 pt-4">
                                    <Button type="button" variant="ghost" @click="showForm = false">
                                        Hủy
                                    </Button>
                                    <Button type="submit" class="text-white font-bold"
                                        :disabled="!canSubmit || form.processing">
                                        <LoaderCircle v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                        Hoàn tất đăng ký
                                    </Button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    </ClientHeaderLayout>
</template>
