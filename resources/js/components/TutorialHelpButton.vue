<template>
    <div v-if="!isHidden" ref="rootRef" class="fixed bottom-6 right-6 z-[70] flex flex-col items-end gap-3">
        <AnimatePresence>
            <motion.div
                v-if="isMenuOpen"
                class="w-64 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-2xl shadow-slate-900/10 backdrop-blur"
                :initial="{ opacity: 0, y: 16, scale: 0.98 }"
                :animate="{ opacity: 1, y: 0, scale: 1 }"
                :exit="{ opacity: 0, y: 12, scale: 0.98 }"
                :transition="{ duration: 0.2, ease: 'easeOut' }"
                role="menu"
            >
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Trợ giúp</p>
                <div class="mt-3 space-y-2">
                    <Link
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        class="group flex items-start gap-2 rounded-xl border border-slate-100 px-3 py-2 text-left transition hover:border-slate-200 hover:bg-slate-50"
                        role="menuitem"
                        @click="closeMenu"
                    >
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-slate-800">{{ link.label }}</span>
                            <span v-if="link.description" class="text-xs text-slate-500">
                                {{ link.description }}
                            </span>
                        </div>
                    </Link>
                </div>
            </motion.div>
        </AnimatePresence>

        <div class="relative flex items-center">
            <AnimatePresence>
                <motion.span
                    v-if="showTooltip"
                    class="pointer-events-none absolute right-full mr-3 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white shadow-lg"
                    :initial="{ opacity: 0, x: 8 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :exit="{ opacity: 0, x: 8 }"
                    :transition="{ duration: 0.2, ease: 'easeOut' }"
                >
                    trợ giúp
                </motion.span>
            </AnimatePresence>

            <motion.button
                type="button"
                class="relative flex h-12 w-12 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg shadow-primary-600/30 focus:outline-none ring-amber-50 ring cursor-pointer focus-visible:ring-2 focus-visible:ring-primary-300"
                :while-hover="buttonMotion.hover"
                :while-tap="buttonMotion.tap"
                :transition="buttonMotion.transition"
                :aria-expanded="hasMultipleLinks ? isMenuOpen : undefined"
                :aria-haspopup="hasMultipleLinks ? 'menu' : undefined"
                aria-label="Trợ giúp"
                @mouseenter="isHovered = true"
                @mouseleave="isHovered = false"
                @focus="isHovered = true"
                @blur="isHovered = false"
                @click="handleClick"
            >
                <HelpCircle class="h-5 w-5" />
                <span
                    v-if="hasMultipleLinks"
                    class="absolute -right-1 -top-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-[11px] font-bold text-primary-700 shadow"
                >
                    {{ links.length }}
                </span>
            </motion.button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { HelpCircle } from 'lucide-vue-next';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useTutorialLinks } from '@/lib/tutorial-helper';

const { links, isHidden } = useTutorialLinks();

const rootRef = ref<HTMLElement | null>(null);
const isHovered = ref(false);
const isMenuOpen = ref(false);
const hasMultipleLinks = computed(() => links.value.length > 1);
const showTooltip = computed(() => isHovered.value && !isMenuOpen.value);

const buttonMotion = {
    hover: { y: -2, scale: 1.04 },
    tap: { scale: 0.96 },
    transition: { type: 'spring', stiffness: 260, damping: 18 },
} as const;

const handleClick = () => {
    if (!hasMultipleLinks.value) {
        router.visit(links.value[0].href);
        return;
    }
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const handleDocumentClick = (event: MouseEvent) => {
    if (!rootRef.value) {
        return;
    }
    if (!rootRef.value.contains(event.target as Node)) {
        isMenuOpen.value = false;
    }
};

watch(hasMultipleLinks, (value) => {
    if (!value) {
        isMenuOpen.value = false;
    }
});

onMounted(() => {
    document.addEventListener('click', handleDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleDocumentClick);
});
</script>
