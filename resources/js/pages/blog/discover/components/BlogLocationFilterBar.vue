<template>
    <section class="rounded-3xl border border-gray-100 bg-gray-50 px-4 py-4 text-sm text-gray-700 sm:px-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1 space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Tỉnh / Thành phố</label>
                <select
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100"
                    :value="provinceValue"
                    @change="onProvinceChange($event.target.value)"
                >
                    <option value="">Tất cả</option>
                    <option v-for="province in provinces" :key="province.id" :value="province.id">{{ province.name }}</option>
                </select>
            </div>

            <div class="flex-1 space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Quận / Huyện</label>
                <select
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100"
                    :value="districtValue"
                    :disabled="!provinceValue"
                    @change="onDistrictChange($event.target.value)"
                >
                    <option value="">Tất cả</option>
                    <option v-for="district in districts" :key="district.value" :value="district.value">{{ district.name }}</option>
                </select>
                <p v-if="loading" class="text-xs text-gray-500">Đang tải quận/huyện...</p>
            </div>

            <button
                v-if="hasActiveFilter"
                type="button"
                class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600 transition hover:border-gray-300 hover:text-primary-600"
                @click="clearFilters"
            >
                Xóa lọc
            </button>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface ProvinceOption {
    id: number;
    name: string;
}

interface DistrictOption {
    value: string;
    name: string;
}

const props = withDefaults(defineProps<{
    provinces: ProvinceOption[];
    provinceId?: string | null;
    districts: DistrictOption[];
    districtId?: string | null;
    loading?: boolean;
}>(), {
    provinces: () => [],
    provinceId: null,
    districts: () => [],
    districtId: null,
    loading: false,
});

const emit = defineEmits<{
    'update:provinceId': [value: string | null];
    'update:districtId': [value: string | null];
    clear: [];
}>();

const provinceValue = computed(() => props.provinceId ?? '');
const districtValue = computed(() => props.districtId ?? '');
const hasActiveFilter = computed(() => Boolean(provinceValue.value || districtValue.value));

function onProvinceChange(value: string) {
    const normalized = value || null;
    emit('update:provinceId', normalized);
}

function onDistrictChange(value: string) {
    const normalized = value || null;
    emit('update:districtId', normalized);
}

function clearFilters() {
    emit('clear');
}
</script>
