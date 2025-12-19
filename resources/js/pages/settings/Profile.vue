<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { getImg } from '../booking/helper';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

interface ProfileTranslations {
    breadcrumb: string;
    head_title: string;
    heading: {
        title: string;
        description: string;
    };
    form: {
        partner_name_label: string;
        sections: {
            avatar: {
                title: string;
                description: string;
                change: string;
                remove: string;
                helper: string;
            };
            contact: {
                title: string;
                description: string;
            };
        };
        name: {
            label: string;
            placeholder: string;
        };
        email: {
            label: string;
            placeholder: string;
        };
        country_code: {
            label: string;
            placeholder: string;
        };
        phone: {
            label: string;
            placeholder: string;
        };
        bio: {
            label: string;
            placeholder: string;
        };
        unverified_notice: string;
        resend_verification: string;
        verification_sent: string;
        submit: string;
        success: string;
    };
}

defineProps<Props>();

const page = usePage<AppPageProps<{ translations: ProfileTranslations }>>();
const translations = computed(() => page.props.translations as ProfileTranslations);
const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: translations.value.breadcrumb,
        href: '/settings/profile',
    },
]);
const user = computed(() => page.props.auth.user);
const flashSuccess = computed(() => page.props.flash?.success ?? null);

const displayName = computed(() => {
    const current = user.value;
    if (!current?.name) {
        return '';
    }
    if (current.partner_profile_name) {
        return `${current.name} (${translations.value.form.partner_name_label}: ${current.partner_profile_name})`;
    }
    return current.name;
});

const userInitials = computed(() => {
    const name = user.value?.name;
    if (!name) {
        return '?';
    }
    return name
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0]?.toUpperCase())
        .join('')
        .slice(0, 2) || name[0]?.toUpperCase() || '?';
});

const form = useForm({
    name: '',
    email: '',
    country_code: '',
    phone: '',
    bio: '',
    avatar: null as File | null,
});

const avatarPreview = ref<string | null>(null);
const tempObjectUrl = ref<string | null>(null);
const avatarInput = ref<HTMLInputElement | null>(null);

const clearTempObjectUrl = () => {
    if (tempObjectUrl.value) {
        URL.revokeObjectURL(tempObjectUrl.value);
        tempObjectUrl.value = null;
    }
};

const syncWithUser = () => {
    form.name = user.value?.name ?? '';
    form.email = user.value?.email ?? '';
    form.country_code = user.value?.country_code ?? '';
    form.phone = user.value?.phone ?? '';
    form.bio = user.value?.bio ?? '';
    form.avatar = null;
    avatarPreview.value = user.value?.avatar_url ?? null;
    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
    clearTempObjectUrl();
};

watch(
    () => user.value,
    () => {
        syncWithUser();
    },
    { immediate: true },
);

const handleAvatarChange = (event: Event) => {
    const files = (event.target as HTMLInputElement).files;
    if (!files || files.length === 0) {
        form.avatar = null;
        avatarPreview.value = user.value?.avatar_url ?? null;
        return;
    }

    const file = files[0];
    form.avatar = file;
    clearTempObjectUrl();
    const objectUrl = URL.createObjectURL(file);
    tempObjectUrl.value = objectUrl;
    avatarPreview.value = objectUrl;
};

const removeSelectedAvatar = () => {
    form.avatar = null;
    avatarPreview.value = user.value?.avatar_url ?? null;
    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
    clearTempObjectUrl();
};

onBeforeUnmount(() => {
    clearTempObjectUrl();
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            _method: 'patch',
        }))
        .post(route('profile.update'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                clearTempObjectUrl();
                form.reset('avatar');
                if (avatarInput.value) {
                    avatarInput.value.value = '';
                }
            },
            onFinish: () => {
                form.transform((data) => data);
            },
        });
};

const hasPendingAvatar = computed(() => Boolean(form.avatar));
</script>

<template>
    <ClientHeaderLayout :show-footer="false">
        <AppLayout :breadcrumbs="breadcrumbItems">

            <Head :title="translations.head_title" />

            <SettingsLayout>
                <div class="flex flex-col space-y-6">
                    <HeadingSmall :title="translations.heading.title" :description="translations.heading.description" />

                    <div class="rounded-2xl border border-neutral-200/60 bg-white/90 shadow-sm backdrop-blur">
                        <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-10 p-6 lg:p-8">
                            <div class="space-y-1">
                                <h2 class="text-lg font-semibold text-neutral-900">
                                    {{ displayName || translations.heading.title }}
                                </h2>
                                <p class="text-sm text-neutral-500">
                                    {{ translations.heading.description }}
                                </p>
                            </div>

                            <div class="grid gap-8">
                                <section class="space-y-4">
                                    <div class="space-y-3">
                                        <h3 class="text-sm font-semibold text-neutral-700">
                                            {{ translations.form.sections.avatar.title }}
                                        </h3>
                                        <p class="text-sm text-neutral-500">
                                            {{ translations.form.sections.avatar.description }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex flex-col items-center justify-center gap-5 rounded-xl border border-dashed border-neutral-300 bg-neutral-50/70 p-6 text-center">
                                        <div
                                            class="relative h-28 w-28 overflow-hidden rounded-full border-4 border-white shadow-xl ring-1 ring-black/5">
                                            <img v-if="avatarPreview" :src="getImg(avatarPreview)"
                                                :alt="user?.name ?? 'User avatar'" class="h-full w-full object-cover" loading="lazy" />
                                            <div v-else
                                                class="flex h-full w-full items-center justify-center bg-neutral-200 text-2xl font-semibold text-neutral-500">
                                                {{ userInitials }}
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-center gap-3">
                                            <input ref="avatarInput" type="file" name="avatar"
                                                accept="image/png,image/jpeg,image/jpg" class="hidden"
                                                @change="handleAvatarChange" />

                                            <Button type="button" variant="outline" size="lg"
                                                :disabled="form.processing" @click="avatarInput?.click()">
                                                {{ translations.form.sections.avatar.change }}
                                            </Button>

                                            <button v-if="hasPendingAvatar" type="button"
                                                class="text-sm font-medium text-red-600 transition hover:text-red-500"
                                                @click="removeSelectedAvatar">
                                                {{ translations.form.sections.avatar.remove }}
                                            </button>

                                            <p class="text-xs text-neutral-500">
                                                {{ translations.form.sections.avatar.helper }}
                                            </p>
                                        </div>
                                    </div>

                                    <InputError class="text-left text-sm" :message="form.errors.avatar" />
                                </section>

                                <section class="space-y-6">
                                    <div class="space-y-3">
                                        <h3 class="text-sm font-semibold text-neutral-700">
                                            {{ translations.form.sections.contact.title }}
                                        </h3>
                                        <p class="text-sm text-neutral-500">
                                            {{ translations.form.sections.contact.description }}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div class="grid gap-2">
                                            <Label for="name">{{ translations.form.name.label }}</Label>
                                            <Input id="name" name="name" class="mt-1 w-full" v-model="form.name"
                                                required autocomplete="name"
                                                :placeholder="translations.form.name.placeholder" />
                                            <InputError class="mt-1" :message="form.errors.name" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="email">{{ translations.form.email.label }}</Label>
                                            <Input id="email" type="email" name="email" class="mt-1 w-full"
                                                v-model="form.email" required autocomplete="username"
                                                :placeholder="translations.form.email.placeholder" />
                                            <InputError class="mt-1" :message="form.errors.email" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="country_code">{{ translations.form.country_code.label }}</Label>
                                            <Input id="country_code" name="country_code" class="mt-1 w-full"
                                                v-model="form.country_code" autocomplete="country"
                                                :placeholder="translations.form.country_code.placeholder" />
                                            <InputError class="mt-1" :message="form.errors.country_code" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="phone">{{ translations.form.phone.label }}</Label>
                                            <Input id="phone" type="tel" name="phone" class="mt-1 w-full"
                                                v-model="form.phone" autocomplete="tel"
                                                :placeholder="translations.form.phone.placeholder" />
                                            <InputError class="mt-1" :message="form.errors.phone" />
                                        </div>

                                        <div class="grid gap-2 md:col-span-2">
                                            <Label for="bio">{{ translations.form.bio.label }}</Label>
                                            <textarea id="bio" name="bio" rows="4"
                                                class="mt-1 w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm shadow-sm transition focus-visible:border-primary-400 focus-visible:ring-2 focus-visible:ring-primary-200 focus-visible:outline-none"
                                                v-model="form.bio"
                                                :placeholder="translations.form.bio.placeholder"></textarea>
                                            <InputError class="mt-1" :message="form.errors.bio" />
                                        </div>
                                    </div>

                                    <div v-if="mustVerifyEmail && !user?.email_verified_at"
                                        class="rounded-lg border border-amber-200 bg-amber-50/90 p-4 text-sm text-amber-800">
                                        <p>
                                            {{ translations.form.unverified_notice }}
                                            <Link :href="route('verification.send')" method="post" as="button"
                                                class="ml-1 font-medium text-amber-900 underline decoration-dotted underline-offset-4 transition hover:text-amber-700">
                                                {{ translations.form.resend_verification }}
                                            </Link>
                                        </p>

                                        <p v-if="status === 'verification-link-sent'"
                                            class="mt-2 font-medium text-amber-700">
                                            {{ translations.form.verification_sent }}
                                        </p>
                                    </div>
                                </section>

                                <section class="space-y-4 pt-6 border-t border-gray-100 dark:border-gray-800">
                                    <div
                                        class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100">
                                        <div class="space-y-1">
                                            <h3 class="text-base font-semibold text-indigo-900">
                                                Đăng ký làm nhân sự
                                            </h3>
                                            <p class="text-sm text-indigo-600/80">
                                                Trở thành đối tác của chúng tôi để gia tăng thu nhập
                                            </p>
                                        </div>
                                        <Button as-child variant="default"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-md shadow-indigo-200">
                                            <Link :href="route('partner.register.from-client.create')">
                                                Đăng ký ngay
                                            </Link>
                                        </Button>
                                    </div>
                                </section>
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                <Button type="submit" class="text-white font-semibold" :disabled="form.processing">
                                    <span v-if="form.processing"
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-white/60 border-r-transparent"></span>
                                    {{ translations.form.submit }}
                                </Button>

                                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                    <p v-if="form.recentlySuccessful || flashSuccess" class="text-sm text-neutral-600">
                                        {{ flashSuccess || translations.form.success }}
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- <DeleteUser /> -->
            </SettingsLayout>
        </AppLayout>
    </ClientHeaderLayout>
</template>
