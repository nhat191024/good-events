<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <ClientHeaderLayout>
        <AuthBase title="Đăng nhập" description="Đăng nhập hoặc đăng ký để tiếp tục">

            <Head title="Log in" />

            <div v-if="status" class="mb-4 text-sm font-medium text-center text-green-600">
                {{ status }}
            </div>

            <Form method="post" :action="route('login')" :reset-on-success="['password']"
                v-slot="{ errors, processing }" class="flex flex-col gap-6">
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="email">Email hoặc SĐT</Label>
                        <Input id="email" type="email" name="email" required autofocus :tabindex="1"
                            autocomplete="email" placeholder="email@example.com, 097654321" />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password">Mật khẩu</Label>
                            <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm"
                                :tabindex="5">
                                Quên mật khẩu?
                            </TextLink>
                        </div>
                        <Input id="password" type="password" name="password" required :tabindex="2"
                            autocomplete="current-password" placeholder="Password" />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="remember" class="flex items-center space-x-3">
                            <Checkbox id="remember" name="remember" :tabindex="3" />
                            <span>Ghi nhớ đăng nhập</span>
                        </Label>
                    </div>

                    <Button type="submit" class="w-full mt-4 font-bold text-white" :tabindex="4" :disabled="processing">
                        <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                        Đăng nhập
                    </Button>
                </div>

                <div class="text-sm text-center text-muted-foreground">
                    Chưa có tài khoản?
                    <TextLink :href="route('register')" :tabindex="5">Đăng ký</TextLink>
                </div>
            </Form>
        </AuthBase>
    </ClientHeaderLayout>
</template>
