<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, LoaderCircle, Mail, MessageCircle } from 'lucide-vue-next';
import { inject } from 'vue';

const route = inject('route') as any;

defineProps<{
    hasVerifiedEmail: boolean;
    hasVerifiedPhone: boolean;
    email: string;
    phone: string;
}>();

const form = useForm({
    method: 'email',
});

const submit = () => {
    form.post(route('verification.method.store'));
};
</script>

<template>
    <AuthLayout title="Xác thực tài khoản" description="Chọn phương thức bạn muốn dùng để xác thực tài khoản.">
        <Head title="Xác thực tài khoản" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="flex flex-col gap-3">
                <!-- Email option -->
                <button
                    v-if="!hasVerifiedEmail"
                    type="button"
                    class="group relative flex items-center gap-4 rounded-xl border-2 p-4 text-left transition-all duration-200"
                    :class="
                        form.method === 'email'
                            ? 'border-primary bg-primary/5 dark:bg-primary/10'
                            : 'border-border bg-background hover:border-primary/40 hover:bg-muted/50'
                    "
                    @click="form.method = 'email'"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full transition-colors"
                        :class="
                            form.method === 'email'
                                ? 'bg-primary text-white'
                                : 'bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary'
                        "
                    >
                        <Mail class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-foreground">Xác thực qua Email</p>
                        <p class="mt-0.5 truncate text-sm text-muted-foreground">{{ email }}</p>
                    </div>
                    <CheckCircle2
                        class="h-5 w-5 shrink-0 transition-all duration-200"
                        :class="form.method === 'email' ? 'scale-100 text-primary opacity-100' : 'scale-75 opacity-0'"
                    />
                    <input type="radio" value="email" v-model="form.method" class="sr-only" />
                </button>

                <!-- Phone option -->
                <button
                    v-if="!hasVerifiedPhone"
                    type="button"
                    class="group relative flex items-center gap-4 rounded-xl border-2 p-4 text-left transition-all duration-200"
                    :class="
                        form.method === 'phone'
                            ? 'border-primary bg-primary/5 dark:bg-primary/10'
                            : 'border-border bg-background hover:border-primary/40 hover:bg-muted/50'
                    "
                    @click="form.method = 'phone'"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full transition-colors"
                        :class="
                            form.method === 'phone'
                                ? 'bg-primary text-white'
                                : 'bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary'
                        "
                    >
                        <MessageCircle class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-foreground">Xác thực qua Zalo</p>
                        <p class="mt-0.5 truncate text-sm text-muted-foreground">Gửi mã OTP đến {{ phone }}</p>
                    </div>
                    <CheckCircle2
                        class="h-5 w-5 shrink-0 transition-all duration-200"
                        :class="form.method === 'phone' ? 'scale-100 text-primary opacity-100' : 'scale-75 opacity-0'"
                    />
                    <input type="radio" value="phone" v-model="form.method" class="sr-only" />
                </button>
            </div>

            <div class="flex flex-col gap-3">
                <Button type="submit" class="w-full font-semibold" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Tiếp tục
                </Button>

                <TextLink
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="mx-auto flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Đăng xuất
                </TextLink>
            </div>
        </form>
    </AuthLayout>
</template>
