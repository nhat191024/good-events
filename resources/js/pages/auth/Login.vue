<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { useTutorialHelper } from '@/lib/tutorial-helper';
import { tutorialQuickLinks } from '@/lib/tutorial-links';
import { Form, Head, Link } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const { addTutorialRoutes } = useTutorialHelper();

const showPassword = ref(false);
const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const redirectToProvider = (provider: string) => {
    window.location.href = route('socialite.redirect', provider);
};
// setTutorialRoutes([tutorialQuickLinks.clientRegister, tutorialQuickLinks.partnerRegister]);
// addTutorialRoutes([tutorialQuickLinks.clientRegister, tutorialQuickLinks.partnerRegister]);
</script>

<template>
    <AuthBase title="Đăng nhập" description="">

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-center text-green-600">
            {{ status }}
        </div>

        <Form method="post" :action="route('login')" :reset-on-success="['password']" v-slot="{ errors, processing }"
            class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email hoặc SĐT</Label>
                    <Input id="email" type="email" name="email" required autofocus :tabindex="1" autocomplete="email"
                        placeholder="email@example.com, 097654321" />
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
                    <div class="relative">
                        <Input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                            :tabindex="2" autocomplete="current-password" placeholder="Password" class="pr-10" />
                        <button type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground focus:outline-none"
                            :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'" :tabindex="6"
                            @click="togglePasswordVisibility">
                            <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Ghi nhớ đăng nhập</span>
                    </Label>
                </div>

                <Button type="submit" class="w-full mt-4 font-bold text-white hover:bg-primary-700 active:bg-primary-800 cursor-pointer" :tabindex="4" :disabled="processing">
                    <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                    Đăng nhập
                </Button>

            </div>

            <Button type="button" class="w-full font-bold text-white hover:bg-primary-700 active:bg-primary-800 cursor-pointer" :tabindex="4" :disabled="processing" @click="redirectToProvider('google')">
                <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                Đăng nhập với Google
            </Button>

            <div class="text-sm text-center text-muted-foreground">
                Chưa có tài khoản?
                <TextLink :href="route('register')" :tabindex="7">Đăng ký</TextLink>
            </div>
            <!-- <div class="text-sm text-center text-muted-foreground">
                Bạn là quản trị viên?
                <a href="/admin/login" class="underline text-gray-800 underline-offset-4"> Đến trang quản trị</a>
            </div> -->
            <hr>
            <div class="text-sm text-center text-muted-foreground">
                Hoặc, bạn đang tìm việc?
            </div>
            <Button type="button" class="p-0 w-full mb-4 font-bold text-white bg-green-700 hover:bg-green-800 active:bg-green-900" :tabindex="9" :disabled="processing">
                <!-- <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" /> -->
                <Link :href="route('partner.register')" class="w-full h-full p-2">Đăng ký đối tác</Link>
            </Button>
        </Form>
    </AuthBase>
</template>
