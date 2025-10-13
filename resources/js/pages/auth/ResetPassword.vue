<script setup lang="ts">
    import InputError from '@/components/InputError.vue';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import AuthLayout from '@/layouts/AuthLayout.vue';
    import { Form, Head } from '@inertiajs/vue3';
    import { LoaderCircle } from 'lucide-vue-next';
    import { ref } from 'vue';

    const props = defineProps<{
        token: string;
        email: string;
    }>();

    const inputEmail = ref(props.email);
</script>

<template>
    <AuthLayout title="Đặt lại mật khẩu" description="Vui lòng nhập mật khẩu mới của bạn bên dưới">

        <Head title="Đặt lại mật khẩu" />

        <Form
            method="post"
            :action="route('password.store')"
            :transform="(data) => ({ ...data, token, email })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        v-model="inputEmail"
                        class="block w-full mt-1"
                        readonly
                    />
                    <InputError :message="errors.email" class="mt-2" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Mật khẩu mới</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        class="block w-full mt-1"
                        autofocus
                        placeholder="Nhập mật khẩu mới"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Xác nhận mật khẩu</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="block w-full mt-1"
                        placeholder="Nhập lại mật khẩu"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button type="submit" class="w-full mt-4" :disabled="processing">
                    <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                    Đặt lại mật khẩu
                </Button>
            </div>
        </Form>
    </AuthLayout>
</template>
