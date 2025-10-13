<script setup lang="ts">
    import { Head, useForm, usePage } from '@inertiajs/vue3';
    import { route } from 'ziggy-js';
    import { toISODate } from '../../lib/helper';
    import { PartnerCategory, Event, Ward, Province, WardTypeSelectBox } from '@/types/database';
    import { confirm } from '@/composables/useConfirm'
    import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
    import SelectPartnerHeader from '@/pages/booking/layout/Header.vue'
    import FormGroupLayout from '@/pages/booking/layout/FormGroup.vue'
    import FormItemLayout from '@/pages/booking/layout/FormItem.vue'
    import DatePickerSingle from '@/components/date-picker/DatePicker.vue'
    import TimePickerSingle from '@/components/time-picker/TimePicker.vue'
    import SelectBox from '@/components/Select.vue'
    import Input from '@/components/ui/input/Input.vue'
    import Button from '@/components/ui/button/Button.vue'
    import { reactive, ref, watch } from 'vue';
    import { showLoading, hideLoading } from '@/composables/useLoading'
    import { getImg } from './helper';

    const pageProps = usePage().props

    type PartnerBillForm = {
        order_date: Date | null
        start_time: string
        end_time: string
        province_id: string | null
        ward_id: string | null
        event_id: string | null
        event_custom: string | null
        category_id: number | null
        location_detail: string | number | undefined
        note: string
    }

    // parent
    const partnerCategory = pageProps.partnerCategory as PartnerCategory
    const partnerChildrenCategory = pageProps.partnerChildrenCategory as PartnerCategory
    const eventListProp = pageProps.eventList as Event[]
    const provinceListProp = pageProps.provinces as Province[]
    const eventList = [{ name: 'DS nội dung sự kiện', children: eventListProp.map(event => ({ name: event.name, value: String(event.id) })) }]
    const provinceList = [{ name: 'Chọn tỉnh thành', children: provinceListProp.map(province => ({ name: province.name, value: String(province.id) })) }]

    const LS_KEY = `quick-booking:partner-form:ls`

    const initial : PartnerBillForm = JSON.parse(localStorage.getItem(LS_KEY) || 'null') ?? {
        order_date: null,
        start_time: '',
        end_time: '',
        province_id: null,
        ward_id: null,
        event_id: null,
        event_custom: null,
        category_id: null,
        location_detail: '',
        note: '',
    }

    const location = reactive({
        provinceId: null as string | null,
    })

    const wardList = ref<WardTypeSelectBox[]>([])
    const loadingWards = ref(false)
    const wardsError = ref('')
    const lastProvinceProcessed = ref<string | null>(null)

    watch(() => location.provinceId, async (provinceId, old) => {
        if (!provinceId) {
            wardList.value = []
            lastProvinceProcessed.value = null
            return
        }

        if (lastProvinceProcessed.value === provinceId) {
            return
        }
        lastProvinceProcessed.value = provinceId

        form.ward_id = null
        wardList.value = []
        wardsError.value = ''
        loadingWards.value = true

        form.province_id = provinceId

        try {
            const res = await fetch(`/api/locations/${provinceId}/wards`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
            })
            if (!res.ok) throw new Error(`http ${res.status}`)
            const data = await res.json()
            wardList.value = data.map((w: Ward) => ({ name: w.name, value: String(w.id) }))
        } catch (err) {
            wardsError.value = 'không tải được danh sách phường/xã'
            console.error(err)
        } finally {
            loadingWards.value = false
        }
    })

    const headerImageSrc = getImg(partnerChildrenCategory.media)
    const title = 'Điền thông tin thuê chi tiết'
    const subtitle = `Bạn đã chọn đối tác '${partnerCategory.name}' - Cụ thể là '${partnerChildrenCategory.name}', hãy điền đầy đủ thông tin dưới đây nhé`

    const form = useForm<PartnerBillForm>(initial)

    watch(() => form.data(), (val) => {
        try {
            localStorage.setItem(LS_KEY, JSON.stringify(val))            
        } catch (e) {
            console.error('cannot write ls', e)
        }
    }, { deep: true })

    function submit() {
        form.transform(() => ({
            order_date: toISODate(form.order_date),
            start_time: form.start_time,
            end_time: form.end_time,
            province_id: form.province_id,
            ward_id: form.ward_id,
            event_id: form.event_id,
            event_custom: form.event_custom,
            location_detail: form.location_detail,
            note: form.note,
            category_id: partnerChildrenCategory.id
        }))
        .post(route('quick-booking.save-info'), {
            preserveScroll: true,
            onBefore: () => {
                showLoading({ title: 'Đang tải', message: 'Đợi xíu nhé' })
            },
            onSuccess: () => {
                clearStorage()
                form.reset()
            },
            onFinish: () => {
                hideLoading(true)
            }
        })
    }

    async function onGoBack() {
        const ok = await confirm({
            title: 'Chọn lại kiểu đối tác?',
            message: 'Thông tin đã nhập <b>sẽ được lưu lại</b>!',
            okText: 'Đồng ý!',
            cancelText: 'Không, tôi ổn'
        })

        if (ok) {
            window.history.back()
        }
    }

    function clearStorage() {
        localStorage.removeItem(LS_KEY)
        form.reset('order_date','start_time','end_time','province_id','ward_id','event_id','category_id','location_detail','note')
    }
</script>

<!-- quick booking page FINAL step -->
<template>
    <Head title="Đặt show nhanh - Chọn đối tác" />
    <!-- layout -->
    <ClientAppHeaderLayout>
        <SelectPartnerHeader :title="title" :subtitle="subtitle" :header-img-src="headerImageSrc">
            <!-- form here -->
            <form @submit.prevent="submit" :action="route('quick-booking.save-info')"
                class="bg-gray-50 will-change-transform rounded md:rounded-lg flex flex-col items-center max-w-[800px] gap-[20px] w-full md:w-[86%] h-min p-3 md:p-7 relative">
                <FormGroupLayout>
                    <FormItemLayout :for-id="'select-start-time'" :label="'Thời gian bắt đầu'" :error="form.errors.start_time">
                        <TimePickerSingle use24h v-model="form.start_time" :id="'select-start-time'" />
                    </FormItemLayout>

                    <FormItemLayout :for-id="'select-end-time'" :label="'Thời gian kết thúc'" :error="form.errors.end_time">
                        <TimePickerSingle use24h v-model="form.end_time" :id="'select-end-time'" />
                    </FormItemLayout>
                </FormGroupLayout>

                <FormGroupLayout>
                    <!-- date picker  -->
                    <FormItemLayout :for-id="'select-event-date'" :label="'Ngày tổ chức sự kiện'" :error="form.errors.order_date">
                        <DatePickerSingle :id="'select-event-date'" v-model="form.order_date" />
                    </FormItemLayout>
                    <FormItemLayout :for-id="'select-event-type'" :label="'Nội dung sự kiện'" :error="form.errors.event_id??form.errors.event_custom">
                        <SelectBox 
                            :id="'select-event-type'" 
                            v-model="form.event_id" 
                            :custom-value="form.event_custom"
                            @update:custom-value="val => form.event_custom = val"
                            :options="eventList" 
                            :allow-custom="true"
                            placeholder="Chọn nội dung sự kiện..." 
                        />
                    </FormItemLayout>
                </FormGroupLayout>

                <FormGroupLayout>
                    <FormItemLayout :for-id="'optional-note'" :label="'Ghi chú bổ sung (Tùy chọn)'" :error="form.errors.note">
                        <Input placeholder="VD: Cần người mặc đồng phục có tông màu vàng" :id="'optional-note'" v-model="form.note"
                            class="text-black" />
                    </FormItemLayout>
                </FormGroupLayout>

                <FormGroupLayout>
                    <FormItemLayout :for-id="'event-order-location-province'" :label="'Địa điểm tổ chức'" :error="form.errors.province_id">
                        <SelectBox :id="'event-order-location-province'" v-model="location.provinceId" :options="provinceList" placeholder="Chọn Tỉnh thành..." />
                    </FormItemLayout>
                    <FormItemLayout class="hidden md:block" :for-id="'event-order-location-ward'" :label="' '" :error="form.errors.ward_id">
                        <SelectBox :is-enable="location.provinceId !== null" :id="'event-order-location-ward'" v-model="form.ward_id" :options="wardList" placeholder="Chọn xã, phường..." />
                    </FormItemLayout>
                    <FormItemLayout class="block md:hidden" :for-id="'event-order-location-ward-mobile'" :label="''" :error="form.errors.ward_id">
                        <SelectBox :is-enable="location.provinceId !== null" :id="'event-order-location-ward-mobile'" v-model="form.ward_id" :options="wardList" placeholder="Chọn xã, phường..." />
                    </FormItemLayout>
                </FormGroupLayout>

                <FormGroupLayout v-if="form.ward_id !== null" class="p-0 m-0 h-min">
                    <FormItemLayout :for-id="'event-order-location-detail'" :label="'Địa chỉ chi tiết'" :error="form.errors.location_detail">
                        <Input placeholder="Số nhà, đường..." :id="'event-order-location-detail'" v-model="form.location_detail" class="text-black" />
                    </FormItemLayout>
                </FormGroupLayout>

                <FormGroupLayout class="mb-3">
                    <div class="w-3/4 md:w-1/2">
                        <Button type="button" @click="onGoBack" :variant="'outlineWhite'" :size="'lg'" :class="'w-full cursor-pointer'">Chọn lại loại đối tác</Button>
                    </div>
                    <Button type="submit" :variant="'secondary'" :size="'lg'" :class="'w-3/4 md:w-1/2 font-extrabold text-white'">
                        Đặt show ngay
                    </Button>
                </FormGroupLayout>
            </form>
        </SelectPartnerHeader>
    </ClientAppHeaderLayout>
</template>
