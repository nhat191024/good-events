<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { fmt24, from12h, to12h, toMinutes, Props } from '.'

interface ExtendedProps extends Props {
    use24h?: boolean
}

const props = withDefaults(defineProps<ExtendedProps>(), {
    modelValue: null,
    minuteStep: 1,
    id: '',
    placeholder: 'hh:mm AM/PM',
    commitDefaultOnMount: false,
    defaultTime: '09:00',
    use24h: false
})

const emit = defineEmits<{
    'update:modelValue': [value: string | Date | null]
}>()

const showPanel = ref(false)
const containerRef = ref<HTMLElement | null>(null)

const totalMins = ref<number | null>(toMinutes(props.modelValue))

const sel = computed(() => {
    if (totalMins.value == null) return { h12: 12, h24: 0, m: 0, period: 'AM' as const }
    const mins = totalMins.value
    const h24 = Math.floor(mins / 60)
    const m = mins % 60
    const h12 = h24 % 12 || 12
    const period = h24 < 12 ? ('AM' as const) : ('PM' as const)
    return { h12, h24, m, period }
})

const displayValue = computed(() => {
    if (totalMins.value == null) return ''
    const { h24, h12, m, period } = sel.value
    const hh = String(props.use24h ? h24 : h12).padStart(2, '0')
    const mm = String(m).padStart(2, '0')
    return props.use24h ? `${hh}:${mm}` : `${hh}:${mm} ${period}`
})

const hours = computed(() => {
    return props.use24h 
        ? Array.from({ length: 24 }, (_, i) => i)  // 0..23
        : Array.from({ length: 12 }, (_, i) => i + 1) // 1..12
})

const minutes = computed(() => {
    const step = Math.max(1, Math.min(60, props.minuteStep || 1))
    const out: number[] = []
    for (let i = 0; i < 60; i += step) out.push(i)
    return out
})

const periods = ['AM', 'PM'] as const

const handleClickOutside = (event: Event) => {
    const target = event.target as Element
    if (containerRef.value && !containerRef.value.contains(target)) {
        showPanel.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    if (props.commitDefaultOnMount && props.modelValue == null) {
        const d = toMinutes(props.defaultTime) ?? 9 * 60
        totalMins.value = d
        emit('update:modelValue', fmt24(d))
    }
})
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

const handleInputClick = () => {
    showPanel.value = !showPanel.value
}

function pickHour(h: number) {
    if (props.use24h) {
        totalMins.value = h * 60 + sel.value.m
    } else {
        totalMins.value = from12h(h, sel.value.m, sel.value.period)
    }
    emitOut()
}

function pickMinute(m: number) {
    if (props.use24h) {
        totalMins.value = sel.value.h24 * 60 + m
    } else {
        totalMins.value = from12h(sel.value.h12, m, sel.value.period)
    }
    emitOut()
}

function pickPeriod(p: 'AM' | 'PM') {
    totalMins.value = from12h(sel.value.h12, sel.value.m, p)
    emitOut()
}

function emitOut() {
    const mins = totalMins.value
    if (mins === null) return

    const incoming = props.modelValue
    if (incoming instanceof Date) {
        const base = new Date(incoming || new Date())
        base.setHours(Math.floor(mins / 60), mins % 60, 0, 0)
        emit('update:modelValue', base)
    } else {
        emit('update:modelValue', fmt24(mins))
    }
}

watch(() => props.modelValue, (v) => {
    const mins = toMinutes(v)
    totalMins.value = mins
}, { immediate: true })
</script>

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
</style>

<template>
    <div ref="containerRef" class="relative w-full">
        <!-- Input -->
        <div class="relative">
            <input :id="props.id" :value="displayValue" @click="handleInputClick" type="text" 
                :placeholder="props.use24h ? 'hh:mm' : 'hh:mm AM/PM'"
                readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 cursor-pointer
                focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                hover:border-gray-400 transition" />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3M12 22a10 10 0 110-20 10 10 0 010 20z" />
                </svg>
            </div>
        </div>

        <!-- Panel -->
        <transition name="fade-scale">
            <div v-if="showPanel" class="absolute top-full left-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50 p-3"
                :class="props.use24h ? 'w-56' : 'w-80'">
                <!-- Header chips -->
                <div class="flex gap-2 mb-2">
                    <span
                        class="px-2 py-1 rounded-md text-white bg-primary-500 text-xs font-semibold select-none flex-1 text-center">
                        {{ String(props.use24h ? sel.h24 : sel.h12).padStart(2, '0') }}
                    </span>
                    <span
                        class="px-2 py-1 rounded-md text-white bg-primary-500 text-xs font-semibold select-none flex-1 text-center">
                        {{ String(sel.m).padStart(2, '0') }}
                    </span>
                    <span v-if="!props.use24h"
                        class="px-2 py-1 rounded-md text-white bg-primary-500 text-xs font-semibold select-none flex-1 text-center">
                        {{ sel.period }}
                    </span>
                </div>

                <!-- Columns: 24h mode has 2 columns, 12h mode has 3 columns -->
                <div :class="props.use24h ? 'grid-cols-2' : 'grid-cols-3'" class="grid gap-2">
                    <!-- hours -->
                    <div class="max-h-64 overflow-auto px-1 pt-1" style="scrollbar-width: none;">
                        <button type="button" v-for="h in hours" :key="`h-${h}`" @click="pickHour(h)" class="w-full h-9 rounded-md text-sm transition flex items-center justify-center
                        hover:ring-2 hover:ring-primary-400 hover:ring-offset-2 hover:ring-offset-white
                        active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500" :class="{
                            'bg-primary-500 text-white hover:bg-primary-600': props.use24h ? sel.h24 === h : sel.h12 === h,
                            'text-gray-900 hover:bg-gray-100': props.use24h ? sel.h24 !== h : sel.h12 !== h
                        }">
                            {{ String(h).padStart(2, '0') }}
                        </button>
                    </div>

                    <!-- minutes -->
                    <div class="max-h-64 overflow-auto px-1 pt-1" style="scrollbar-width: none;">
                        <button type="button" v-for="m in minutes" :key="`m-${m}`" @click="pickMinute(m)" class="w-full h-9 rounded-md text-sm transition flex items-center justify-center
                        hover:ring-2 hover:ring-primary-400 hover:ring-offset-2 hover:ring-offset-white
                        active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500" :class="{
                            'bg-primary-500 text-white hover:bg-primary-600': sel.m === m,
                            'text-gray-900 hover:bg-gray-100': sel.m !== m
                        }">
                            {{ String(m).padStart(2, '0') }}
                        </button>
                    </div>

                    <!-- AM/PM (only in 12h mode) -->
                    <div v-if="!props.use24h" class="max-h-64 overflow-auto px-1 pt-1">
                        <button type="button" v-for="p in periods" :key="`p-${p}`" @click="pickPeriod(p)" class="w-full h-9 rounded-md text-sm transition flex items-center justify-center
                        hover:ring-2 hover:ring-primary-400 hover:ring-offset-2 hover:ring-offset-white
                        active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500" :class="{
                            'bg-primary-500 text-white hover:bg-primary-600': sel.period === p,
                            'text-gray-900 hover:bg-gray-100': sel.period !== p
                        }">
                            {{ p }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>