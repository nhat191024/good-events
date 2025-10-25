<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

interface PasswordTranslations {
    breadcrumb: string;
    head_title: string;
    heading: {
        title: string;
        description: string;
    };
    form: {
        current: {
            label: string;
            placeholder: string;
        };
        new: {
            label: string;
            placeholder: string;
        };
        confirmation: {
            label: string;
            placeholder: string;
        };
        submit: string;
        success: string;
    };
}

const page = usePage<AppPageProps<{ translations: PasswordTranslations }>>();
const translations = computed(() => page.props.translations as PasswordTranslations);
const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: translations.value.breadcrumb,
        href: '/settings/password',
    },
]);

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);
</script>

<template>
    <ClientHeaderLayout :show-footer="false">

        <AppLayout :breadcrumbs="breadcrumbItems">

            <Head :title="translations.head_title" />

            <SettingsLayout>
                <div class="space-y-6">
                    <HeadingSmall :title="translations.heading.title" :description="translations.heading.description" />

                    <Form method="put" :action="route('password.update')" :options="{
                        preserveScroll: true,
                    }" reset-on-success :reset-on-error="['password', 'password_confirmation', 'current_password']"
                        class="space-y-6" v-slot="{ errors, processing, recentlySuccessful }">
                        <div class="grid gap-2">
                            <Label for="current_password">{{ translations.form.current.label }}</Label>
                            <Input id="current_password" ref="currentPasswordInput" name="current_password"
                                type="password" class="block w-full mt-1" autocomplete="current-password"
                                :placeholder="translations.form.current.placeholder" />
                            <InputError :message="errors.current_password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">{{ translations.form.new.label }}</Label>
                            <Input id="password" ref="passwordInput" name="password" type="password"
                                class="block w-full mt-1" autocomplete="new-password"
                                :placeholder="translations.form.new.placeholder" />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">{{ translations.form.confirmation.label }}</Label>
                            <Input id="password_confirmation" name="password_confirmation" type="password"
                                class="block w-full mt-1" autocomplete="new-password"
                                :placeholder="translations.form.confirmation.placeholder" />
                            <InputError :message="errors.password_confirmation" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button class="text-white" :disabled="processing">{{ translations.form.submit }}</Button>

                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="recentlySuccessful" class="text-sm text-neutral-600">
                                    {{ translations.form.success }}
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </div>
            </SettingsLayout>
        </AppLayout>

    </ClientHeaderLayout>
</template>
