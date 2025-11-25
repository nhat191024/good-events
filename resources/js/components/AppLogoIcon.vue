<script setup lang="ts">
    import { AppSettings } from '@/types';
    import { usePage } from '@inertiajs/vue3';
    import { computed, type HTMLAttributes } from 'vue';

    const page = usePage()
    const settings = computed(() => page.props.app_settings as AppSettings)

    defineOptions({
        inheritAttrs: false,
    });

    interface Props {
        className?: HTMLAttributes['class'];
    }

    defineProps<Props>();
</script>

<template>
    <img :src="getImg(`/${settings.app_logo}`)" alt="Sukientot" class="h-9 w-9 rounded-full object-contain ring-2 ring-white/40" />
</template>
