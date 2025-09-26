<script setup lang="ts">
    import Icon from '@/components/Icon.vue'
    import RoundMedia from './RoundMedia.vue';

    interface Props {
        title: string;
        description: string;
        cardImgSrc?: string;
        iconName?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        title: 'Title here',
        description: 'Description here',
        iconName: 'partyPopper'
    });
</script>

<template>
    <!-- large card -->
    <div class="items-center self-start flex gap-[10px] md:h-[361px] h-min relative p-0 text-decoration-none w-full hover:scale-105 transition-all">
        <div class="h-full relative w-[1px] grow shrink-0 basis-0">
            <div class="h-full w-full items-center flex flex-col gap-0 p-0 relative">
                <div class="border-1 rounded-lg bg-primary-50 border-primary-400 justify-center will-change-transform transform-none items-center flex grow shrink-0 basis-0 flex-col flex-nowrap w-full min-h-[200px] md:py-[70px] py-6 md:px-[10px] px-1 gap-[5px] md:gap-[10px]">
                    <!-- big icon -->
                    <RoundMedia :src="props.cardImgSrc" :size="{ base: 60, sm: 90, md: 110 }" :inner-scale="1" shape="circle" innerBgClass="bg-primary-50" outerBgClass="bg-transparent" :bordered="false">
                        <!-- fallback to icon -->
                        <div
                            class="p-5 bg-white items-center flex grow-0 shrink-0 basis-auto gap-[5px] h-min overflow-visible relative w-min rounded-full">
                            <Icon :name="props.iconName" :class="'relative text-black md:w-[69px] md:h-[69px] w-[39px] h-[39px] stroke-primary-800'" />
                        </div>
                    </RoundMedia>

                    <div
                        class="items-center flex grow-0 shrink-0 basis-auto gap-[5px] h-min overflow-visible p-0 relative w-full">
                        <div class="outline-0 flex flex-col shrink-0 transform-none grow-0 basis-auto h-fit relative text-wrap w-full wrap-break-word">
                            <h3 v-text="props.title" class="font-lexend text-black md:text-lg text-xs font-bold text-center z-[9]"></h3>
                        </div>
                    </div>
                    <div
                        class="items-center flex grow-0 shrink-0 basis-auto gap-[5px] h-min overflow-visible p-0 relative w-full">
                        <div class="outline-0 flex flex-col justify-center shrink-0 transform-none grow-0 basis-auto h-auto relative text-wrap w-full break-words">
                            <h3 v-text="props.description" class="font-lexend md:text-md text-xs text-center text-gray-600"></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
