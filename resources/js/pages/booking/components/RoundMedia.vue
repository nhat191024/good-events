<script setup lang="ts">
    import { computed, ref } from 'vue'
import { getImg } from '../helper';

    type RespSize = number | { base: number; sm?: number; md?: number; lg?: number }

    const props = withDefaults(defineProps<{
        src?: string
        alt?: string
        size?: RespSize
        innerScale?: number
        shape?: 'circle' | 'rounded' | 'square'
        outerBgClass?: string
        innerBgClass?: string
        bordered?: boolean
        borderClass?: string
        class?: string
        fit?: 'cover' | 'contain'
    }>(), {
        src: '',
        alt: '',
        size: 100,
        innerScale: 0.83,
        shape: 'rounded',
        outerBgClass: 'bg-[#FFE6E6]',
        innerBgClass: 'bg-primary-100',
        bordered: true,
        borderClass: 'border border-primary-100',
        fit: 'contain',
    })

    const clampedScale = computed(() => Math.max(0.2, Math.min(0.95, props.innerScale || 0.83)))

    const sizeVars = computed(() => {
        if (typeof props.size === 'number') {
            return { '--sz': `${props.size}px` } as Record<string, string>
        }
        const { base, sm, md, lg } = props.size!
        const vars: Record<string, string> = { '--sz': `${base}px` }
        if (sm != null) vars['--sz-sm'] = `${sm}px`
        if (md != null) vars['--sz-md'] = `${md}px`
        if (lg != null) vars['--sz-lg'] = `${lg}px`
        return vars
    })

    const outerStyle = computed(() => sizeVars.value)
    const innerStyle = computed(() => {
        const p = clampedScale.value * 100
        return { width: `${p}%`, height: `${p}%` }
    })

    const shapeClass = computed(() => {
        if (props.shape === 'square') return ''
        if (props.shape === 'rounded') return 'rounded-xl'
        return 'rounded-full'
    })

    const fitClass = computed(() => (props.fit === 'cover' ? 'object-cover' : 'object-contain'))

    const hasError = ref(false);
    function onError () {
        hasError.value = true;
    }
</script>

<template>
    <div class="rm-wrapper inline-flex items-center justify-center relative shrink-0"
        :class="[shapeClass, outerBgClass, bordered ? borderClass : '', props.class]" :style="outerStyle">
        <div class="overflow-hidden flex items-center justify-center" :class="[shapeClass, innerBgClass]"
            :style="innerStyle">
            <img v-if="src && !hasError" @error.once="onError" :src="getImg(src)" :alt="alt || 'media'" class="w-full h-full select-none pointer-events-none"
                :class="[fitClass, shapeClass]" draggable="false" />
            <div v-else class="flex items-center justify-center w-full h-full">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* base */
.rm-wrapper {
    width: var(--sz);
    height: var(--sz);
}

/* sm >= 640px */
@media (min-width: 640px) {
    .rm-wrapper {
        width: var(--sz-sm, var(--sz));
        height: var(--sz-sm, var(--sz));
    }
}

/* md >= 768px */
@media (min-width: 768px) {
    .rm-wrapper {
        width: var(--sz-md, var(--sz-sm, var(--sz)));
        height: var(--sz-md, var(--sz-sm, var(--sz)));
    }
}

/* lg >= 1024px */
@media (min-width: 1024px) {
    .rm-wrapper {
        width: var(--sz-lg, var(--sz-md, var(--sz-sm, var(--sz))));
        height: var(--sz-lg, var(--sz-md, var(--sz-sm, var(--sz))));
    }
}
</style>
