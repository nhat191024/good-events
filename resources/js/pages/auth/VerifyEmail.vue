<script setup lang="ts">
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Form, Head } from '@inertiajs/vue3'
import { LoaderCircle } from 'lucide-vue-next'

defineProps<{
    status?: string
}>()
</script>

<template>
    <AuthLayout title="Xác thực Email"
        description="Vui lòng xác thực địa chỉ email bằng cách nhấn vào liên kết mà chúng tôi đã gửi cho bạn.">

        <Head title="Xác thực Email" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            Một liên kết xác thực mới đã được gửi đến địa chỉ email bạn đã đăng ký.
        </div>

        <Form method="post" :action="route('verification.send')" class="space-y-6 text-center" v-slot="{ processing }">
            <Button :disabled="processing" variant="secondary" class="text-white font-bold">
                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                Gửi lại email xác thực
            </Button>

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm">
                Đăng xuất
            </TextLink>
        </Form>
    </AuthLayout>
</template>
