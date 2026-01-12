<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthBase from '@/layouts/AuthLayout.vue'
import FormGroupLayout from '@/pages/booking/layout/FormGroup.vue'
import FormItemLayout from '@/pages/booking/layout/FormItem.vue'
import SelectBox from '@/components/Select.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next'
import { reactive, ref, watch, computed } from 'vue'
import { route } from '@/utils/ziggy'
import type { Province, Ward, WardTypeSelectBox } from '@/types/database'
import axios from 'axios'
import { useTutorialHelper } from '@/lib/tutorial-helper'
import { tutorialQuickLinks } from '@/lib/tutorial-links'

const pageProps = usePage().props
const provinceListProp = pageProps.provinces as Province[] | undefined
const { setTutorialRoutes } = useTutorialHelper();

// fallback an toàn nếu thiếu props
const provinceList = [
    {
        name: 'Chọn tỉnh thành',
        children: (provinceListProp ?? []).map((p) => ({ name: p.name, value: String(p.id) })),
    },
]

const form = useForm({
    name: '',
    email: '',
    phone: '',
    identity_card_number: '',
    ward_id: null as string | null,
    password: '',
    password_confirmation: '',
})

const location = reactive({
    provinceId: null as string | null,
})

const wardList = ref<WardTypeSelectBox[]>([])
const loadingWards = ref(false)
const wardsError = ref('')
const lastProvinceProcessed = ref<string | null>(null)
const showPassword = ref(false)
const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value
}

watch(
    () => location.provinceId,
    async (provinceId) => {
        // clear khi bỏ chọn
        if (!provinceId) {
            wardList.value = []
            lastProvinceProcessed.value = null
            form.ward_id = null
            return
        }

        // tránh gọi trùng
        if (lastProvinceProcessed.value === provinceId) return
        lastProvinceProcessed.value = provinceId

        // reset UI
        form.ward_id = null
        wardList.value = []
        wardsError.value = ''
        loadingWards.value = true

        try {
            const response = await axios.get<Ward[]>(`/api/locations/${provinceId}/wards`, {
                headers: { Accept: 'application/json' },
            })
            const data = response.data
            wardList.value = data.map((w) => ({ name: w.name, value: String(w.id) }))
        } catch (err) {
            wardsError.value = 'không tải được danh sách phường/xã'
            console.error(err)
        } finally {
            loadingWards.value = false
        }
    }
)

const canSubmit = computed(() => {
    return (
        !!form.name &&
        !!form.email &&
        !!form.phone &&
        !!form.identity_card_number &&
        !!form.password &&
        !!form.password_confirmation &&
        !!form.ward_id &&
        !loadingWards.value
    )
})

function submit() {
    // check nhanh phía client để UX mượt
    if (!form.ward_id) {
        form.setError('ward_id', 'hãy chọn phường/xã')
        return
    }

    form.post(route('partner.register.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password', 'password_confirmation')
            router.get('filament.partner.pages.profile-settings')
        },
    })
}

// setTutorialRoutes([tutorialQuickLinks.partnerRegister]);
</script>

<template>
    <AuthBase title="Tạo tài khoản đối tác" description="Nhập thông tin để trở thành đối tác của chúng tôi!">

        <Head title="Đăng ký đối tác" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Họ và tên</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name"
                        v-model="form.name" placeholder="Nhập họ và tên đầy đủ" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Địa chỉ email</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email"
                        placeholder="email@vídụ.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Số điện thoại</Label>
                    <Input id="phone" type="tel" required :tabindex="3" autocomplete="tel" v-model="form.phone"
                        placeholder="0987654321" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="identity_card_number">Số CCCD</Label>
                    <Input id="identity_card_number" type="text" required :tabindex="4" autocomplete="off"
                        v-model="form.identity_card_number" placeholder="034758372993" />
                    <InputError :message="form.errors.identity_card_number" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Mật khẩu</Label>
                    <div class="relative">
                        <Input id="password" :type="showPassword ? 'text' : 'password'" required :tabindex="5"
                            autocomplete="new-password" v-model="form.password" placeholder="Nhập mật khẩu"
                            class="pr-10" />
                        <button type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground focus:outline-none"
                            :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'" :tabindex="6"
                            @click="togglePasswordVisibility">
                            <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Xác nhận mật khẩu</Label>
                    <div class="relative">
                        <Input id="password_confirmation" :type="showPassword ? 'text' : 'password'" required
                            :tabindex="7" autocomplete="new-password" v-model="form.password_confirmation"
                            placeholder="Nhập lại mật khẩu" class="pr-10" />
                        <button type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground focus:outline-none"
                            :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'" :tabindex="8"
                            @click="togglePasswordVisibility">
                            <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- chọn tỉnh / phường-xã -->
                <FormGroupLayout>
                    <FormItemLayout :for-id="'event-order-location-province'" :label="'Tỉnh thành'">
                        <SelectBox :id="'event-order-location-province'" v-model="location.provinceId"
                            :options="provinceList" placeholder="Chọn Khu vực hoạt động của bạn..." />
                    </FormItemLayout>
                </FormGroupLayout>

                <FormGroupLayout>
                    <FormItemLayout class="hidden md:block" :for-id="'event-order-location-ward'"
                        :label="'Phường, Huyện'" :error="form.errors.ward_id || wardsError">
                        <SelectBox :is-enable="location.provinceId !== null" :id="'event-order-location-ward'"
                            v-model="form.ward_id" :options="wardList" :loading="loadingWards"
                            placeholder="Chọn xã, phường..." />
                    </FormItemLayout>
                    <FormItemLayout class="block md:hidden" :for-id="'event-order-location-ward-mobile'"
                        :label="'Phường, Huyện'" :error="form.errors.ward_id || wardsError">
                        <SelectBox :is-enable="location.provinceId !== null" :id="'event-order-location-ward-mobile'"
                            v-model="form.ward_id" :options="wardList" :loading="loadingWards"
                            placeholder="Chọn xã, phường..." />
                    </FormItemLayout>
                </FormGroupLayout>

                <Button type="submit" class="w-full mt-2 text-white font-bold"
                    :disabled="!canSubmit || form.processing">
                    <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <span v-else> Tạo tài khoản </span>
                </Button>
            </div>

            <div class="text-sm text-center text-muted-foreground">
                Đã có tài khoản?
                <TextLink :href="route('login')" class="underline underline-offset-4"> Đăng nhập </TextLink>
            </div>
            <hr />
            <div class="text-sm text-center text-muted-foreground"> Bạn không muốn làm đối tác? </div>
            <Button type="button"
                class="p-0 w-full mb-4 font-bold text-white bg-cyan-700 hover:bg-cyan-800 active:bg-cyan-900">
                <Link :href="route('register')" class="w-full text-white font-bold h-full p-2">Đăng ký tài khoản khách
                </Link>
            </Button>
        </form>
    </AuthBase>
</template>
