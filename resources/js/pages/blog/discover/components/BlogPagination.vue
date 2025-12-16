<template>
    <motion.footer
        :initial="footerMotion.initial"
        :animate="footerMotion.animate"
        class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 pt-6 text-sm text-gray-600 sm:flex-row">
        <div>
            Trang {{ pagination.current_page }} / {{ pagination.last_page }} — {{ pagination.total }} bài viết
        </div>
        <div class="flex items-center gap-2">
            <motion.div
                :whileHover="buttonHover"
                :whilePress="buttonPress">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="pagination.current_page <= 1"
                    @click="emit('change', pagination.current_page - 1)"
                >
                    Trang trước
                </Button>
            </motion.div>
            <motion.div
                :whileHover="buttonHover"
                :whilePress="buttonPress">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="emit('change', pagination.current_page + 1)"
                >
                    Trang sau
                </Button>
            </motion.div>
        </div>
    </motion.footer>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { motion } from 'motion-v';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{
    pagination: PaginationMeta;
}>();

const emit = defineEmits<{
    change: [page: number];
}>();

const footerMotion = {
    initial: {
        opacity: 0.5,
        y: 18,
    },
    animate: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.45,
            ease: 'easeOut',
        },
    },
} as const;

const buttonHover = {
    y: -2,
    scale: 1.02,
    transition: {
        type: 'spring',
        stiffness: 260,
        damping: 18,
    },
} as const;

const buttonPress = {
    scale: 0.96,
    transition: {
        type: 'spring',
        stiffness: 360,
        damping: 28,
    },
} as const;
</script>
