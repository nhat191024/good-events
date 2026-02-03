<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import { tutorialQuickLinks } from '@/lib/tutorial-links';
import { useTutorialHelper } from '@/lib/tutorial-helper';
import { inject } from "vue";

const route = inject('route') as any;

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

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmationPassword = ref(false);

const { addTutorialRoutes } = useTutorialHelper();
addTutorialRoutes([
    tutorialQuickLinks.clientBecomePartner,
    tutorialQuickLinks.partnerUpdateStaffInfo,
    tutorialQuickLinks.partnerLoginEvent,
]);
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
                            <div class="relative">
                                <Input id="current_password" ref="currentPasswordInput" name="current_password"
                                    :type="showCurrentPassword ? 'text' : 'password'" class="block w-full mt-1 pr-10"
                                    autocomplete="current-password"
                                    :placeholder="translations.form.current.placeholder" />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                                    @click="showCurrentPassword = !showCurrentPassword">
                                    <Eye v-if="!showCurrentPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="errors.current_password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">{{ translations.form.new.label }}</Label>
                            <div class="relative">
                                <Input id="password" ref="passwordInput" name="password"
                                    :type="showNewPassword ? 'text' : 'password'" class="block w-full mt-1 pr-10"
                                    autocomplete="new-password" :placeholder="translations.form.new.placeholder" />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                                    @click="showNewPassword = !showNewPassword">
                                    <Eye v-if="!showNewPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">{{ translations.form.confirmation.label }}</Label>
                            <div class="relative">
                                <Input id="password_confirmation" name="password_confirmation"
                                    :type="showConfirmationPassword ? 'text' : 'password'"
                                    class="block w-full mt-1 pr-10" autocomplete="new-password"
                                    :placeholder="translations.form.confirmation.placeholder" />
                                <button type="button"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                                    @click="showConfirmationPassword = !showConfirmationPassword">
                                    <Eye v-if="!showConfirmationPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
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
