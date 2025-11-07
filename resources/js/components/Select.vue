<script setup lang="ts">
/**
 * select.vue (with custom option at top)
 * - supports grouped options (Group[]) or flat options (Option[])
 * - supports emitting an event when the custom option is selected
 * - uses createSearchFilter + tokenize + highlight from lib/search-filter
 */

import { ref, computed, watch, onMounted, onBeforeUnmount, PropType } from 'vue'
import { ChevronDown, Check } from 'lucide-vue-next'
import { createSearchFilter, tokenize, highlight } from '@/lib/search-filter'

/* types */
type Option = { name: string; value?: string }
type Group = { name: string; children: Option[] }

const CUSTOM_OPTION_VALUE = '__CUSTOM__'

/* props */
const props = defineProps({
    modelValue: {
        type: String as PropType<string | null>,
        default: null,
    },
    options: {
        type: Array as PropType<Group[] | Option[]>,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Placeholder...',
    },
    id: {
        type: String,
        default: ''
    },
    isEnable: {
        type: Boolean,
        default: true
    },
    allowCustom: {
        type: Boolean,
        default: false
    },
    customOptionLabel: {
        type: String,
        default: 'Nhập giá trị tùy chỉnh'
    }
})

const emit = defineEmits<{
    'update:modelValue': [val: string | null]
    change: [val: string | null]
    'custom-option-selected': [selected: boolean]
}>()

/* ui state */
const open = ref(false)
const query = ref('')
const rootEl = ref<HTMLElement | null>(null)
const inputEl = ref<HTMLInputElement | null>(null)
const highlightedIndex = ref(-1)
const customSelected = ref(false)

const isGroupShape = computed(() => {
    const src = props.options as any[]
    return Array.isArray(src) && src.length > 0 && (src[0] as any).children !== undefined
})

const normalizedGroups = computed<Group[]>(() => {
    const src = props.options as any[] || []
    if (isGroupShape.value) {
        return (src as Group[]).map(g => ({
            name: g.name ?? '',
            children: (g.children || []).map((c: any) => ({ name: String(c.name ?? ''), value: c.value == null ? String(c.name ?? '') : String(c.value) }))
        }))
    } else {
        return [{
            name: '',
            children: (src as Option[]).map(o => ({ name: String(o.name ?? ''), value: o.value == null ? String(o.name ?? '') : String(o.value) }))
        }]
    }
})

const tokens = computed(() => tokenize(query.value))

type FlatItem =
    | { type: 'group'; label: string }
    | { type: 'option'; label: string; value: string }
    | { type: 'custom'; label: string; value: string }

const filtered = computed<FlatItem[]>(() => {
    const groups = normalizedGroups.value
    const predicate = createSearchFilter<{ label: string; group: string }>(['label', 'group'], query.value, 'AND')

    const out: FlatItem[] = []

    if (props.allowCustom) {
        out.push({ type: 'custom', label: props.customOptionLabel, value: CUSTOM_OPTION_VALUE })
    }

    for (const g of groups) {
        const kids = (g.children || []).map(c => ({
            label: c.name,
            value: (c.value ?? c.name),
            group: g.name ?? ''
        }))

        const matched = kids.filter(k => predicate(k))
        if (matched.length) {
            out.push({ type: 'group', label: g.name })
            matched.forEach(m => out.push({ type: 'option', label: m.label, value: m.value }))
        }
    }

    return out
})

const displayLabel = computed(() => {
    const val = props.modelValue

    if (customSelected.value) {
        return props.customOptionLabel
    }

    for (const g of normalizedGroups.value) {
        for (const c of (g.children || [])) {
            if ((c.value ?? c.name) === val) return c.name
        }
    }
    return val ?? ''
})

const toggle = () => {
    open.value = !open.value
    if (open.value) nextTickFocus()
}

function nextTickFocus() {
    requestAnimationFrame(() => {
        inputEl.value?.focus()
        const list = filtered.value || []
        const idxSelected = list.findIndex(it => it.type === 'option' && it.value === props.modelValue)
        const idxFirstOpt = list.findIndex(it => it.type === 'option' || it.type === 'custom')
        highlightedIndex.value = idxSelected >= 0 ? idxSelected : idxFirstOpt
    })
}

function close() {
    open.value = false
}

function onDocClick(e: MouseEvent) {
    if (!rootEl.value) return
    if (!rootEl.value.contains(e.target as Node)) {
        close()
    }
}
onMounted(() => document.addEventListener('mousedown', onDocClick))
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocClick))

function selectValue(val: string) {
    if (val === CUSTOM_OPTION_VALUE) {
        customSelected.value = true
        emit('custom-option-selected', true)
        emit('update:modelValue', null)
        emit('change', null)
        close()
        return
    }
    customSelected.value = false
    emit('custom-option-selected', false)
    emit('update:modelValue', val)
    emit('change', val)
    close()
}

function onKeydown(e: KeyboardEvent) {
    const navKeys = ['ArrowDown', 'ArrowUp', 'Enter', 'Escape', ' ']

    if (!open.value && (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ')) {
        e.preventDefault(); open.value = true; nextTickFocus(); return
    }
    if (!open.value) return
    if (!navKeys.includes(e.key)) return

    if (e.key === 'Escape') {
        e.preventDefault()
        close()
    }
    else if (e.key === 'ArrowDown') { e.preventDefault(); move(1) }
    else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1) }
    else if (e.key === 'Enter') {
        e.preventDefault()
        const item = filtered.value[highlightedIndex.value]
        if (item && (item.type === 'option' || item.type === 'custom')) selectValue(item.value)
    }
}

function move(delta: number) {
    const list = filtered.value
    if (!list.length) return
    let i = highlightedIndex.value
    let guard = 0
    do {
        i = (i + delta + list.length) % list.length
        guard++
        if (guard > list.length + 2) break
    } while (list[i].type === 'group')
    highlightedIndex.value = i
}

watch(open, (v) => {
    if (v) requestAnimationFrame(() => inputEl.value?.focus())
    else {
        query.value = ''
    }
})

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        customSelected.value = false
    }
})

function highlightHtml(label: string, toks: string[]) {
    return highlight(label, toks)
}
</script>

<template>
    <div ref="rootEl" class="relative w-full">
        <!-- anchor button -->
        <button type="button" v-bind:disabled="!isEnable" :id="props.id" @click="toggle" @keydown="onKeydown" :class="(isEnable ? 'bg-white hover:bg-stone-50' : 'bg-gray-300')" class="min-w-[160px] w-full inline-flex items-center justify-between rounded-lg border px-3 h-10 gap-2
                text-sm text-gray-800 shadow-sm focus:outline-none border-gray-300
                focus:ring-2 focus:ring-primary-500 focus:border-primary-500" :aria-expanded="open">
            <span class="truncate text-left text-gray-600" v-if="!displayLabel">
                {{ placeholder }}
            </span>
            <span class="truncate" v-else>{{ displayLabel }}</span>
            <ChevronDown class="h-4 w-4 text-gray-700 transition-transform" :class="open ? 'rotate-180' : ''" />
        </button>

        <!-- dropdown -->
        <transition name="fade-scale">
            <div v-if="open" class="absolute z-10 w-full mt-2 bg-white overflow-hidden rounded-lg shadow-sm border border-gray-300
                    ring-1 ring-black/5" @keydown.stop="onKeydown">

                <!-- search box -->
                <div class="p-2 border-b border-gray-300">
                    <input ref="inputEl" v-model="query" type="text" placeholder="Tìm kiếm..." class="w-full text-black h-8 px-2 rounded-md border bg-white text-sm
                    focus:outline-none focus:ring-2 focus:ring-primary-500 border-gray-300 focus:border-primary-500" />
                </div>

                <div class="max-h-64 overflow-auto py-1">
                    <template v-if="filtered.length">
                        <div v-for="(item, i) in filtered"
                            :key="item.type === 'group' ? 'g-' + item.label : item.type + '-' + item.value">
                            <div v-if="item.type === 'group'" class="px-3 py-1 text-xs text-gray-500 select-none">
                                {{ item.label }}
                            </div>

                            <button v-else type="button" @click="selectValue(item.value)"
                                @mouseenter="highlightedIndex = i"
                                class="w-full h-8 px-3 pr-8 text-left text-sm flex items-center relative hover:bg-primary-50"
                                :class="[
                                    i === highlightedIndex ? 'bg-primary-100/70' : '',
                                    item.type === 'custom' ? 'text-primary-600 italic font-medium' : 'text-gray-800',
                                    item.type === 'option' && item.value === props.modelValue ? 'text-primary-700 font-medium' : ''
                                ]">
                                <span class="truncate" v-html="highlightHtml(item.label, item.type === 'custom' ? [] : tokens)"></span>
                                <Check v-if="item.type === 'option' && item.value === props.modelValue" class="absolute right-2 h-4 w-4" />
                            </button>
                        </div>
                    </template>

                    <div v-else class="px-3 py-2 text-xs text-gray-500 text-center">Không có kết quả</div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: transform .15s ease, opacity .15s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
    transform: scale(0.98);
    opacity: 0;
}
</style>
