<script setup lang="ts">
    import InputError from '@/components/InputError.vue';
    import TextLink from '@/components/TextLink.vue';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import AuthLayout from '@/layouts/AuthLayout.vue';
    import { Form, Head } from '@inertiajs/vue3';
    import { LoaderCircle, CheckCircle2 } from 'lucide-vue-next';
    import { ref, watch } from 'vue';

    const showSuccessMessage = ref(false);
    
    const props = defineProps<{
        status?: string;
    }>();

    watch(
        () => props.status,
        (newStatus) => {
            if (newStatus) {
                showSuccessMessage.value = true;
                setTimeout(() => {
                    showSuccessMessage.value = false;
                }, 5000);
            }
        }
    );
</script>

<template>
    <AuthLayout title="Lấy lại mật khẩu" description="Nhập email của bạn để nhận link đặt lại mặt khẩu">

        <Head title="Forgot password" />

        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100" leave-to-class="opacity-0 translate-y-1">
            <div v-if="showSuccessMessage && status"
                class="mb-4 flex items-center gap-2 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-700 border border-green-200">
                <CheckCircle2 class="h-5 w-5 flex-shrink-0" />
                <span>{{ status }}</span>
            </div>
        </Transition>

        <div class="space-y-6">
            <Form method="post" :action="route('password.email')" v-slot="{ errors, processing }">
                <div class="grid gap-2">
                    <Label for="email">Địa chỉ Email</Label>
                    <Input id="email" type="email" name="email" autocomplete="off" autofocus
                        placeholder="email@example.com" :disabled="processing" required />
                    <InputError :message="errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="processing" type="submit">
                        <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ processing ? 'Đang gửi...' : 'Xác nhận' }}
                    </Button>
                </div>
            </Form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Hoặc, quay lại</span>
                <TextLink :href="route('login')">đăng nhập</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
