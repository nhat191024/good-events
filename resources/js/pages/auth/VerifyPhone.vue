<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useForm, Head } from '@inertiajs/vue3'
import { LoaderCircle, Send } from 'lucide-vue-next'
import { ref, inject, onMounted } from "vue";

const route = inject('route') as any;

const props = defineProps<{
    status?: string
}>()

const resendForm = useForm({});
const form = useForm({
    otp: ''
});

const isResendDisabled = ref(false);
const resendCountdown = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const startCooldown = (seconds = 120) => {
    isResendDisabled.value = true;
    resendCountdown.value = seconds;

    if (timer) clearInterval(timer);

    timer = setInterval(() => {
        resendCountdown.value--;
        if (resendCountdown.value <= 0) {
            isResendDisabled.value = false;
            if (timer) clearInterval(timer);
        }
    }, 1000);
};

const sendOtp = () => {
    resendForm.post(route('verification.phone.send'), {
        preserveScroll: true,
        onSuccess: () => startCooldown()
    });
};

const submitOtp = () => {
    form.post(route('verification.phone.verify'));
};

const formattedTime = () => {
    const min = Math.floor(resendCountdown.value / 60);
    const sec = resendCountdown.value % 60;
    return `${min}:${sec.toString().padStart(2, '0')}`;
};

onMounted(() => {
    // startCooldown(5); // Testing only
});
</script>

<template>
    <AuthLayout title="Xác thực Số điện thoại"
        description="Chúng tôi đã gửi một mã OTP qua tin nhắn Zalo đến số điện thoại của bạn. Vui lòng nhập mã để xác thực.">

        <Head title="Xác thực Số điện thoại" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            Một mã OTP mới đã được gửi đến số điện thoại của bạn.
        </div>

        <div v-if="resendForm.errors.otp" class="mb-4 text-center text-sm font-medium text-red-600">
            {{ resendForm.errors.otp }}
        </div>

        <form @submit.prevent="submitOtp" class="flex flex-col gap-6">
            <div class="grid gap-2">
                <Label for="otp" class="text-center">Nhập mã OTP</Label>
                <Input id="otp" type="text" required autofocus
                    :tabindex="1"
                    autocomplete="one-time-code"
                    v-model="form.otp"
                    placeholder="Nhập mã 6 số"
                    class="text-center text-2xl tracking-[0.5em] font-mono h-14"
                    maxlength="6" />
                <InputError :message="form.errors.otp" class="text-center" />
            </div>

            <Button type="submit" class="w-full" :tabindex="2" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Xác thực
            </Button>

            <div class="flex items-center justify-between text-sm text-muted-foreground mt-2">
                <button type="button" @click="sendOtp" :disabled="isResendDisabled || resendForm.processing" tabindex="3"
                    class="flex items-center text-primary hover:underline hover:text-primary/80 disabled:opacity-50 disabled:no-underline cursor-pointer bg-transparent border-none">
                    <LoaderCircle v-if="resendForm.processing" class="mr-1 h-3 w-3 animate-spin" />
                    <Send v-else class="mr-1 h-3 w-3" />
                    {{ isResendDisabled ? `Gửi lại sau ${formattedTime()}` : 'Gửi lại mã OTP' }}
                </button>

                <TextLink :href="route('verification.method')" tabindex="4">
                    Quay lại chọn phương thức
                </TextLink>
            </div>

            <hr class="my-2">

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm" tabindex="5">
                Đăng xuất
            </TextLink>
        </form>
    </AuthLayout>
</template>
