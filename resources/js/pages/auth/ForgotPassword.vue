<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Eye,
    EyeOff,
    KeyRound,
    LoaderCircle,
    Mail,
    MessageCircle,
    Phone,
} from 'lucide-vue-next';
import { computed, inject, ref, watch } from 'vue';

const route = inject('route') as any;

const props = defineProps<{
    status?: string;
    forgotStep?: 'select' | 'email-input' | 'email-sent' | 'phone-input' | 'phone-otp' | 'phone-reset';
    forgotCredential?: string;
    forgotMethod?: 'email' | 'phone';
}>();

type Step = 'select' | 'email-input' | 'email-sent' | 'phone-input' | 'phone-otp' | 'phone-reset';

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string>) ?? {});

const step = ref<Step>('select');
const selectedMethod = ref<'email' | 'phone'>('email');
const emailValue = ref('');
const phoneValue = ref('');
const otpValues = ref(['', '', '', '', '', '']);
const newPassword = ref('');
const confirmPassword = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const processing = ref(false);

watch(
    () => props.forgotStep,
    (newStep) => {
        if (newStep) {
            step.value = newStep;
        }
    },
    { immediate: true },
);

watch(
    () => props.forgotMethod,
    (newMethod) => {
        if (newMethod) {
            selectedMethod.value = newMethod;
        }
    },
    { immediate: true },
);

watch(
    () => props.forgotCredential,
    (newCred) => {
        if (!newCred) {
            return;
        }
        if (props.forgotMethod === 'phone') {
            phoneValue.value = newCred;
        } else if (props.forgotMethod === 'email') {
            emailValue.value = newCred;
        }
    },
    { immediate: true },
);

const stepTitles: Record<Step, { title: string; description: string }> = {
    select: { title: 'Lấy lại mật khẩu', description: 'Chọn phương thức để đặt lại mật khẩu' },
    'email-input': { title: 'Nhập địa chỉ Email', description: 'Chúng tôi sẽ gửi link đặt lại đến email của bạn' },
    'email-sent': { title: 'Kiểm tra email', description: 'Chúng tôi đã gửi link đặt lại mật khẩu cho bạn' },
    'phone-input': { title: 'Nhập số điện thoại', description: 'Chúng tôi sẽ gửi mã OTP đến số điện thoại của bạn' },
    'phone-otp': { title: 'Nhập mã OTP', description: '' },
    'phone-reset': { title: 'Đặt mật khẩu mới', description: 'Tạo mật khẩu mới cho tài khoản của bạn' },
};

const otpDescription = computed(() => `Mã OTP đã được gửi đến ${phoneValue.value}`);

function goBack() {
    if (step.value === 'email-input' || step.value === 'phone-input') {
        step.value = 'select';
    } else if (step.value === 'phone-otp') {
        step.value = 'phone-input';
    } else if (step.value === 'phone-reset') {
        step.value = 'phone-otp';
    }
}

function selectMethod(method: 'email' | 'phone') {
    selectedMethod.value = method;
}

function confirmMethod() {
    step.value = selectedMethod.value === 'email' ? 'email-input' : 'phone-input';
}

function sendCredential() {
    processing.value = true;
    const credential = selectedMethod.value === 'email' ? emailValue.value : phoneValue.value;

    router.post(
        route('password.send'),
        { method: selectedMethod.value, credential },
        {
            preserveState: true,
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function resendOtp() {
    processing.value = true;

    router.post(
        route('password.resend-otp'),
        { phone: phoneValue.value },
        {
            preserveState: true,
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function verifyOtp() {
    processing.value = true;
    const otp = otpValues.value.join('');

    router.post(
        route('password.verify-otp'),
        { phone: phoneValue.value, otp },
        {
            preserveState: true,
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function resetPassword() {
    processing.value = true;

    router.post(
        route('password.reset-phone'),
        {
            password: newPassword.value,
            password_confirmation: confirmPassword.value,
        },
        {
            preserveState: true,
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function handleOtpInput(index: number, event: Event) {
    const target = event.target as HTMLInputElement;
    const value = target.value.replace(/\D/g, '').slice(-1);
    otpValues.value[index] = value;
    if (value && index < 5) {
        const next = document.getElementById(`otp-${index + 1}`);
        next?.focus();
    }
}

function handleOtpKeydown(index: number, event: KeyboardEvent) {
    if (event.key === 'Backspace' && !otpValues.value[index] && index > 0) {
        const prev = document.getElementById(`otp-${index - 1}`);
        prev?.focus();
    }
}

function handleOtpPaste(event: ClipboardEvent) {
    event.preventDefault();
    const text = event.clipboardData?.getData('text').replace(/\D/g, '').slice(0, 6) ?? '';
    text.split('').forEach((char, i) => {
        otpValues.value[i] = char;
    });
    const lastFilled = Math.min(text.length, 5);
    document.getElementById(`otp-${lastFilled}`)?.focus();
}
</script>

<template>
    <AuthLayout
        :title="stepTitles[step].title"
        :description="step === 'phone-otp' ? otpDescription : stepTitles[step].description"
    >
        <Head title="Quên mật khẩu" />

        <!-- Back button for non-select steps -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-x-2"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
            mode="out-in"
        >
            <button
                v-if="step !== 'select' && step !== 'email-sent'"
                class="mb-2 flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors"
                @click="goBack"
            >
                <ArrowLeft class="h-3.5 w-3.5" />
                Quay lại
            </button>
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
            mode="out-in"
        >
            <!-- STEP 1: Select method -->
            <div v-if="step === 'select'" key="select" class="flex flex-col gap-4">
                <button
                    type="button"
                    class="group relative flex items-center gap-4 rounded-xl border-2 p-4 text-left transition-all duration-200"
                    :class="
                        selectedMethod === 'email'
                            ? 'border-primary bg-primary/5 dark:bg-primary/10'
                            : 'border-border bg-background hover:border-primary/40 hover:bg-muted/50'
                    "
                    @click="selectMethod('email')"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full transition-colors"
                        :class="
                            selectedMethod === 'email'
                                ? 'bg-primary text-white'
                                : 'bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary'
                        "
                    >
                        <Mail class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-foreground">Qua Email</p>
                        <p class="mt-0.5 text-sm text-muted-foreground">Nhận link đặt lại qua email</p>
                    </div>
                    <CheckCircle2
                        class="h-5 w-5 shrink-0 transition-all duration-200"
                        :class="selectedMethod === 'email' ? 'scale-100 text-primary opacity-100' : 'scale-75 opacity-0'"
                    />
                </button>

                <button
                    type="button"
                    class="group relative flex items-center gap-4 rounded-xl border-2 p-4 text-left transition-all duration-200"
                    :class="
                        selectedMethod === 'phone'
                            ? 'border-primary bg-primary/5 dark:bg-primary/10'
                            : 'border-border bg-background hover:border-primary/40 hover:bg-muted/50'
                    "
                    @click="selectMethod('phone')"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full transition-colors"
                        :class="
                            selectedMethod === 'phone'
                                ? 'bg-primary text-white'
                                : 'bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary'
                        "
                    >
                        <MessageCircle class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-foreground">Qua Số điện thoại</p>
                        <p class="mt-0.5 text-sm text-muted-foreground">Nhận mã OTP qua Zalo / SMS</p>
                    </div>
                    <CheckCircle2
                        class="h-5 w-5 shrink-0 transition-all duration-200"
                        :class="selectedMethod === 'phone' ? 'scale-100 text-primary opacity-100' : 'scale-75 opacity-0'"
                    />
                </button>

                <Button class="w-full mt-2" @click="confirmMethod">
                    Tiếp tục
                </Button>

                <div class="text-center text-sm text-muted-foreground">
                    <span>Nhớ mật khẩu? </span>
                    <TextLink :href="route('login')">Đăng nhập</TextLink>
                </div>
            </div>

            <!-- STEP 2a: Email input -->
            <div v-else-if="step === 'email-input'" key="email-input" class="flex flex-col gap-4">
                <div class="grid gap-2">
                    <Label for="email">Địa chỉ Email</Label>
                    <Input
                        id="email"
                        v-model="emailValue"
                        type="email"
                        autocomplete="email"
                        autofocus
                        placeholder="email@example.com"
                        :disabled="processing"
                    />
                    <InputError :message="errors.credential" />
                </div>

                <Button class="w-full" :disabled="processing || !emailValue" @click="sendCredential">
                    <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ processing ? 'Đang gửi...' : 'Gửi link đặt lại' }}
                </Button>
            </div>

            <!-- STEP 2b: Email sent confirmation -->
            <div v-else-if="step === 'email-sent'" key="email-sent" class="flex flex-col gap-4">
                <div class="flex flex-col items-center gap-4 rounded-xl border border-border bg-muted/40 p-6 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-background border border-border">
                        <CheckCircle2 class="h-7 w-7 text-primary" />
                    </div>
                    <div class="space-y-1.5">
                        <p class="font-semibold text-foreground">Email đã được gửi!</p>
                        <p class="text-sm text-muted-foreground">
                            Chúng tôi đã gửi link đặt lại mật khẩu đến
                            <span class="font-medium text-foreground">{{ emailValue }}</span>.
                            Vui lòng kiểm tra hộp thư đến.
                        </p>
                    </div>
                </div>

                <p class="text-center text-xs text-muted-foreground">
                    Không nhận được email?
                    <button
                        class="text-primary underline-offset-4 hover:underline"
                        @click="step = 'email-input'"
                    >
                        Gửi lại
                    </button>
                </p>

                <div class="text-center text-sm text-muted-foreground">
                    <TextLink :href="route('login')">Quay lại đăng nhập</TextLink>
                </div>
            </div>

            <!-- STEP 3a: Phone input -->
            <div v-else-if="step === 'phone-input'" key="phone-input" class="flex flex-col gap-4">
                <div class="grid gap-2">
                    <Label for="phone">Số điện thoại</Label>
                    <div class="relative">
                        <Phone class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="phone"
                            v-model="phoneValue"
                            type="tel"
                            autocomplete="tel"
                            autofocus
                            placeholder="0901 234 567"
                            class="pl-9"
                            :disabled="processing"
                        />
                    </div>
                    <InputError :message="errors.credential" />
                </div>

                <Button class="w-full" :disabled="processing || !phoneValue" @click="sendCredential">
                    <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ processing ? 'Đang gửi...' : 'Gửi mã OTP' }}
                </Button>
            </div>

            <!-- STEP 3b: OTP input -->
            <div v-else-if="step === 'phone-otp'" key="phone-otp" class="flex flex-col gap-6">
                <div class="flex justify-center gap-2">
                    <input
                        v-for="(_, i) in otpValues"
                        :id="`otp-${i}`"
                        :key="i"
                        v-model="otpValues[i]"
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        class="h-12 w-10 rounded-lg border border-border bg-background text-center text-lg font-semibold transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-50"
                        :disabled="processing"
                        @input="handleOtpInput(i, $event)"
                        @keydown="handleOtpKeydown(i, $event)"
                        @paste="handleOtpPaste"
                    />
                </div>

                <InputError :message="errors.otp" class="text-center" />

                <Button
                    class="w-full"
                    :disabled="processing || otpValues.some((v) => !v)"
                    @click="verifyOtp"
                >
                    <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ processing ? 'Đang xác thực...' : 'Xác nhận OTP' }}
                </Button>

                <p class="text-center text-xs text-muted-foreground">
                    Không nhận được mã?
                    <button class="text-primary underline-offset-4 hover:underline" :disabled="processing" @click="resendOtp">
                        Gửi lại
                    </button>
                </p>
            </div>

            <!-- STEP 4: New password -->
            <div v-else-if="step === 'phone-reset'" key="phone-reset" class="flex flex-col gap-4">
                <div class="flex flex-col items-center gap-2 mb-2">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                        <KeyRound class="h-6 w-6 text-primary" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="new-password">Mật khẩu mới</Label>
                    <div class="relative">
                        <Input
                            id="new-password"
                            v-model="newPassword"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Tối thiểu 8 ký tự"
                            class="pr-10"
                            :disabled="processing"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                            @click="showPassword = !showPassword"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="confirm-password">Xác nhận mật khẩu</Label>
                    <div class="relative">
                        <Input
                            id="confirm-password"
                            v-model="confirmPassword"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            placeholder="Nhập lại mật khẩu"
                            class="pr-10"
                            :disabled="processing"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                            @click="showConfirmPassword = !showConfirmPassword"
                        >
                            <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button class="w-full" :disabled="processing || !newPassword || !confirmPassword" @click="resetPassword">
                    <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ processing ? 'Đang cập nhật...' : 'Đặt lại mật khẩu' }}
                </Button>
            </div>
        </Transition>
    </AuthLayout>
</template>
