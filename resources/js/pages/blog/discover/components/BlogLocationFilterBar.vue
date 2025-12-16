<template>
    <section class="rounded-3xl border border-gray-100 bg-gray-50 px-4 py-4 text-sm text-gray-700 sm:px-6">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 lg:items-end">
            <div class="space-y-2">
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

            <div class="space-y-2">
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

            <div class="space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Số lượng khách</label>
                <select
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100"
                    :value="maxPeopleValue"
                    @change="onMaxPeopleChange($event.target.value)"
                >
                    <option value="">Tất cả</option>
                    <option value="50">Dưới 50 khách</option>
                    <option value="100">50 - 100 khách</option>
                    <option value="200">100 - 200 khách</option>
                    <option value="500">200 - 500 khách</option>
                    <option value="1000">Trên 500 khách</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Chi tiết địa điểm</label>
                <input
                    type="text"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100"
                    placeholder="Nhập địa điểm..."
                    :value="locationDetailValue"
                    @input="onLocationDetailChange($event.target.value)"
                />
            </div>

            <div class="md:col-span-2 lg:col-span-4 flex justify-end" v-if="hasActiveFilter">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600 transition hover:border-gray-300 hover:text-primary-600"
                    @click="clearFilters"
                >
                    Xóa lọc
                </button>
            </div>
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
    maxPeople?: string | null;
    locationDetail?: string | null;
    loading?: boolean;
}>(), {
    provinces: () => [],
    provinceId: null,
    districts: () => [],
    districtId: null,
    maxPeople: null,
    locationDetail: null,
    loading: false,
});

const emit = defineEmits<{
    'update:provinceId': [value: string | null];
    'update:districtId': [value: string | null];
    'update:maxPeople': [value: string | null];
    'update:locationDetail': [value: string | null];
    clear: [];
}>();

const provinceValue = computed(() => props.provinceId ?? '');
const districtValue = computed(() => props.districtId ?? '');
const maxPeopleValue = computed(() => props.maxPeople ?? '');
const locationDetailValue = computed(() => props.locationDetail ?? '');

const hasActiveFilter = computed(() => Boolean(
    provinceValue.value ||
    districtValue.value ||
    maxPeopleValue.value ||
    locationDetailValue.value
));

function onProvinceChange(value: string) {
    const normalized = value || null;
    emit('update:provinceId', normalized);
}

function onDistrictChange(value: string) {
    const normalized = value || null;
    emit('update:districtId', normalized);
}

function onMaxPeopleChange(value: string) {
    const normalized = value || null;
    emit('update:maxPeople', normalized);
}

function onLocationDetailChange(value: string) {
    const normalized = value || null;
    emit('update:locationDetail', normalized);
}

function clearFilters() {
    emit('clear');
}
</script>
