<script setup lang="ts">
import { usePageLoadingState } from '@/composables/usePageLoading'
import { motion, AnimatePresence } from 'motion-v'

const state = usePageLoadingState()
</script>

<template>
    <AnimatePresence>
        <div v-if="state.isLoading"
            class="fixed inset-0 pointer-events-none z-[9999] flex items-start justify-center pt-[8dvh]">
            <motion.div :initial="{ opacity: 0, scale: 1, y: 10 }" :animate="{ opacity: 1, scale: 1, y: 0 }"
                :exit="{ opacity: 0, scale: 1, y: 10 }" :transition="{ duration: 0.2, ease: 'easeOut' }"
                class="relative bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md rounded-2xl shadow-xl border border-black/5 dark:border-white/10 w-40 h-20 flex items-center justify-center p-0">
                <div class="relative w-7 h-12">
                    <motion.div :animate="{ rotate: 360 }"
                        :transition="{ duration: 1, repeat: Infinity, ease: 'linear' }"
                        class="absolute inset-0 rounded-full" style="
                            padding: 4px; /* Border width */
                            background: conic-gradient(
                                #8b5cf6,
                                #f97316,
                                #eab308,
                                #22c55e,
                                #0ea5e9,
                                #6366f1,
                                #a855f7,
                                #f65c5c,
                                #8b5cf6
                            );
                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                            -webkit-mask-composite: xor;
                            mask-composite: exclude;
                        " />
                </div>
                <h1 class="pl-4">Đang tải</h1>
            </motion.div>
        </div>
    </AnimatePresence>
</template>
