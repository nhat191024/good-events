<template>
    <nav aria-label="Breadcrumb" class="text-xs font-medium uppercase tracking-wide text-primary-600">
        <ul class="flex items-center gap-2">
            <li>
                <Link :href="route('asset.home')" class="hover:text-primary-800">Kho tài liệu</Link>
            </li>
            <li>
                <span aria-hidden="true" class="px-1 text-primary-400">›</span>
                <Link
                    :href="
                        route('asset.show', {
                            category_slug: props.fileProduct.category?.slug ?? 'tai-lieu',
                            file_product_slug: props.fileProduct.slug,
                        })
                    "
                    class="hover:text-primary-800"
                >
                    {{ props.fileProduct.name }}
                </Link>
            </li>
            <li>
                <span aria-hidden="true" class="px-1 text-primary-400">›</span>
                <span class="text-primary-800">Thanh toán</span>
            </li>
        </ul>
    </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import type { Category, FileProduct } from '@/pages/home/types';

interface BreadcrumbProps {
    fileProduct: FileProduct & { category?: Category | null };
}

const props = defineProps<BreadcrumbProps>();
</script>
