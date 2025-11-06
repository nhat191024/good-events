<script setup lang="ts">
    import Button from '@/components/ui/button/Button.vue';
    import Input from '@/components/ui/input/Input.vue';
    import Icon from '@/components/Icon.vue'
    import { ref, watch } from 'vue';

    interface Props {
        modelValue?: string;
        placeholder?: string;
        showSearchBtn?: boolean
    }

    const props = withDefaults(defineProps<Props>(), {
        modelValue: '',
        placeholder: 'Tìm kiếm...',
        showSearchBtn: true
    });

    const emit = defineEmits<{
        'update:modelValue': [value: string];
        'search': [value: string];
    }>();

    const keyword = ref(props.modelValue);

    watch(() => props.modelValue, (newValue) => {
        keyword.value = newValue;
    });

    watch(keyword, (newValue) => {
        emit('update:modelValue', newValue);
    });

    const handleSearch = () => {
        emit('search', keyword.value);
    };

    const handleSubmit = (event: Event) => {
        event.preventDefault();
        handleSearch();
    };
</script>

<template>
    <div class="bg-white h-full w-full rounded-2xl shadow-lg border border-gray-200 flex items-center overflow-visible p-0 relative">
        <div class="z-10 md:px-[15px] px-[9px] items-center flex gap-[10px] h-[40px] overflow-visible relative w-[5%]">
            <div class="w-[24px] cursor-pointer h-[40px] relative" @click="handleSearch">
                <div class="h-full flex rounded w-full items-center">
                    <Icon :name="'search'" :class="'relative text-black w-[19px] h-[19px]'"/>
                </div>
            </div>
        </div>
        <div class="items-center flex flex-row flex-nowrap h-full p-[10px] relative w-full">
            <form @submit="handleSubmit" class="items-center flex flex-row h-full p-0 w-full relative gap-3">
                <div class="flex h-[40px] md:w-[80%] w-[70%]">
                    <Input
                        :placeholder="placeholder"
                        v-model="keyword"
                        type="text"
                        :class="'border-0 text-left w-full h-full whitespace-nowrap overflow-ellipsis text-black p-3'"
                        style="background: white;"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <div class="flex h-[40px] md:w-[30%] w-[30%]" v-if="showSearchBtn">
                    <Button
                        type="submit"
                        variant="default"
                        :class="'bg-primary-600 text-white h-full w-full will-change-transform rounded-lg hover:bg-primary-700 cursor-pointer'"
                    >
                        Tìm kiếm
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
