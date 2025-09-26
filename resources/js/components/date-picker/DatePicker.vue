<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { fmtDDMMYYYY, toDate } from '.';

const props = withDefaults(defineProps<{
    modelValue: Date | null
    id?: string
    autoClosing?: boolean
    autoClosingDelay?: number
}>(), {
    modelValue: null,
    id: '',
    autoClosing: false,
    autoClosingDelay: 250
})

const emit = defineEmits<{
    (e: 'update:modelValue', v: Date | null): void
}>()

const showCalendar = ref(false)
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const containerRef = ref(null)

const justSelected = ref(false)
const CLOSE_DELAY = 700 // ms

// const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December']
// const dayNames = ['S','M','T','W','T','F','S']
const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12']
const dayNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7']

const handleClickOutside = (event: MouseEvent) => {
    if (containerRef.value && !(containerRef.value as HTMLElement).contains(event.target as Node)) {
        showCalendar.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

const daysInMonth = computed(() => new Date(currentYear.value, currentMonth.value + 1, 0).getDate())
const firstDayOfMonth = computed(() => new Date(currentYear.value, currentMonth.value, 1).getDay())

const displayValue = computed(() => {
    const date = toDate(props.modelValue)
    if (!date) return ''
    return fmtDDMMYYYY(date) // always show dd/mm/yyyy
})

const toggleCalendar = () => { showCalendar.value = !showCalendar.value }

const previousMonth = () => {
    if (currentMonth.value === 0) { currentMonth.value = 11; currentYear.value-- }
    else { currentMonth.value-- }
}
const nextMonth = () => {
    if (currentMonth.value === 11) { currentMonth.value = 0; currentYear.value++ }
    else { currentMonth.value++ }
}

const isSelectedDate = (day: number) => {
    const selectedDate = toDate(props.modelValue)
    if (!selectedDate) return false
    return selectedDate.getDate() === day &&
        selectedDate.getMonth() === currentMonth.value &&
        selectedDate.getFullYear() === currentYear.value
}

const confirmText = computed(() => displayValue.value ? `Đã chọn ${displayValue.value}` : '')

const selectDate = (day: number) => {
    const d = new Date(Date.UTC(currentYear.value, currentMonth.value, day))
    emit('update:modelValue', d)

    justSelected.value = true

    if (props.autoClosing) {
        window.setTimeout(() => {
            showCalendar.value = false
            window.setTimeout(() => { justSelected.value = false }, props.autoClosingDelay)
        }, CLOSE_DELAY)
    }
}

watch(() => props.modelValue, (val) => {
    const date = toDate(val)
    if (date) {
        currentMonth.value = date.getMonth()
        currentYear.value = date.getFullYear()
    }
}, { immediate: true })
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <!-- Input -->
        <div class="relative">
            <input :id="props.id" v-model="displayValue" @click="toggleCalendar"
                type="text" placeholder="mm/dd/yyyy" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 cursor-pointer
                focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                hover:border-gray-400 transition" />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Calendar -->
        <transition name="fade-scale">
            <div v-if="showCalendar" class="absolute top-full left-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50 px-2 md:px-4 pb-2 w-72 md:w-80
                ring-1 ring-black/5">
                <!-- Header -->
                <transition name="fade">
                    <div v-if="justSelected && confirmText" class="w-full mt-3 inline-flex items-center gap-2 text-xs text-primary-700 bg-primary-50 border border-primary-200
                        px-2.5 py-1.5 rounded-md select-none" aria-live="polite">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ confirmText }}
                    </div>
                </transition>
                <div class="flex items-center justify-between mb-4 mt-4">
                    <button type="button" @click="previousMonth"
                        class="p-2 rounded-full transition
                        hover:bg-gray-100 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                        aria-label="Previous month">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <h2 class="text-lg font-semibold text-gray-900 select-none">
                        {{ monthNames[currentMonth] }} {{ currentYear }}
                    </h2>

                    <button type="button" @click="nextMonth"
                        class="p-2 rounded-full transition
                        hover:bg-gray-100 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                        aria-label="Next month">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Days of week -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <div v-for="day in dayNames" :key="day"
                        class="text-center text-xs font-medium text-gray-500 py-2 select-none">
                        {{ day }}
                    </div>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-7 gap-1">
                    <!-- blanks -->
                    <div v-for="n in firstDayOfMonth" :key="`empty-${n}`" class="h-10"></div>

                    <!-- days -->
                    <button type="button" v-for="day in daysInMonth" :key="day" @click="selectDate(day)" class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-medium transition
                        hover:ring-2 hover:ring-primary-400 hover:ring-offset-2 hover:ring-offset-white
                        active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500" :class="{
                            'bg-primary-500 text-white hover:bg-primary-600': isSelectedDate(day),
                            'text-gray-900 hover:bg-gray-100': !isSelectedDate(day)
                        }">
                        {{ day }}
                    </button>
                </div>


            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: transform 0.15s ease, opacity 0.15s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
    transform: scale(0.98);
    opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity .15s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
