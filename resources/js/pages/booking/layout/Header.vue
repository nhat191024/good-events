<script setup lang="ts">
    import { usePage } from '@inertiajs/vue3';
    import Icon from '@/components/Icon.vue'
    import RoundMedia from '../components/RoundMedia.vue';
import { getImg } from '../helper';

    const page = usePage()

    const flash = page.props.flash as {
        success?: string
        error?: string
    }

    interface Props {
        title: string;
        subtitle: string;
        headerImgSrc?: string;
        iconName?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        title: 'Title here',
        subtitle: 'Subtitle here',
        iconName: 'partyPopper'
    });
</script>

<template>
    <div
        class="relative flex flex-col items-center w-full h-min max-w-[1200px] gap-[36px] z-1 pl-0 pr-0 pt-[30px] pb-[25px]">

        <!-- large card -->
        <div
            class="flex-col items-center bg-white shadow-md flex h-min max-w-[1200px] relative md:pt-6 pt-3 pb-[40px] md:px-[20px] px-2 w-full md:w-[87%] md:rounded-lg gap-[16px]">

            <!-- big icon -->
            <RoundMedia :src="getImg(headerImgSrc)" :fit="'cover'" :size="{ base: 100, sm: 120, md: 140 }"  shape="circle" innerBgClass="bg-white" outerBgClass="bg-white" :bordered="false">
                <!-- fallback to icon -->
                <div
                    class="border border-primary-100 bg-[#FFE6E6] rounded-full items-center flex flex-row gap-[10px] md:h-[120px] h-[80px] md:w-[120px] w-[80px] relative p-0 place-content-center">
                    <div
                        class="rounded-full bg-primary-100 h-[80%] md:w-[85%] w-[80%] relative gap-[10px] flex items-center place-content-center">
                        <Icon :name="iconName" :class="'relative text-primary-800 w-[42px] h-[50%]'" />
                    </div>
                </div>
            </RoundMedia>

            <!-- titles -->
            <div class="items-center flex flex-col gap-1 h-min overflow-visible p-0 relative w-full">
                <div class="flex flex-col justify-start flex-shrink-0 transform-none h-auto relative break-words">
                    <h1 v-text="title"
                        class="font-lexend text-secondary-text md:text-[1.69rem] text-[1.3rem] font-extrabold text-center w-full"></h1>
                </div>
                <div class="flex flex-col justify-start flex-shrink-0 transform-none h-auto relative break-words w-full">
                    <h1 v-text="subtitle" class="font-lexend text-gray-500 md:text-2xl text-lg font-light text-center w-full"></h1>
                </div>
            </div>

            <!-- flash session error messages  -->
            <div v-if="flash.error" class="p-3 bg-red-100 text-red-800 rounded">
                {{ flash.error }}
            </div>
            <div v-if="flash.success" class="p-3 bg-green-100 text-green-800 rounded">
                {{ flash.success }}
            </div>

            <slot />
        </div>
    </div>
</template>
