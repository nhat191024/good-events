<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
</script>

<template>
    <ClientHeaderLayout>
        <AuthBase title="Tạo tài khoản" description="Nhập thông tin của bạn bên dưới để tạo tài khoản mới">

            <Head title="Đăng ký" />

            <Form method="post" :action="route('register')" :reset-on-success="['password', 'password_confirmation']"
                v-slot="{ errors, processing }" class="flex flex-col gap-6">
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">Họ và tên</Label>
                        <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name"
                            placeholder="Nhập họ và tên đầy đủ" />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Địa chỉ email</Label>
                        <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email"
                            placeholder="email@vídụ.com" />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Số điện thoại</Label>
                        <Input id="phone" type="phone" required :tabindex="2" autocomplete="phone" name="phone"
                            placeholder="0987654321" />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Mật khẩu</Label>
                        <Input id="password" type="password" required :tabindex="3" autocomplete="new-password"
                            name="password" placeholder="Nhập mật khẩu" />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Xác nhận mật khẩu</Label>
                        <Input id="password_confirmation" type="password" required :tabindex="4"
                            autocomplete="new-password" name="password_confirmation" placeholder="Nhập lại mật khẩu" />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <Button type="submit" class="w-full mt-2 text-white font-bold" tabindex="5" :disabled="processing">
                        <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                        Tạo tài khoản
                    </Button>
                </div>

                <div class="text-sm text-center text-muted-foreground">
                    Đã có tài khoản?
                    <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">
                        Đăng nhập
                    </TextLink>
                </div>
            </Form>
        </AuthBase>

    </ClientHeaderLayout>
</template>
