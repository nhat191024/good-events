<template>
    <footer class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 pt-6 text-sm text-gray-600 sm:flex-row">
        <div>
            Trang {{ pagination.current_page }} / {{ pagination.last_page }} — {{ pagination.total }} tài liệu
        </div>
        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="pagination.current_page <= 1"
                @click="emit('change', pagination.current_page - 1)"
            >
                Trang trước
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="emit('change', pagination.current_page + 1)"
            >
                Trang sau
            </Button>
        </div>
    </footer>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';

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
</script>
