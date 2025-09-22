<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import SearchBar from './partials/SearchBar.vue';
import SubcategoryBlock from './partials/SubcategoryBlock.vue';

interface PartnerCategory {
  id: number;
  name: string;
  slug: string;
  min_price: number | null;
  max_price: number | null;
  image: string | null;
}

interface ChildCategory {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  partner_categories: PartnerCategory[];
}

interface ParentCategory {
  id: number;
  name: string;
  slug: string;
  description: string | null;
}

interface Props {
  parent: ParentCategory;
  children: ChildCategory[];
  filters: { q: string };
}

const props = defineProps<Props>();

const placeholderAvatar = (text: string) =>
  `https://ui-avatars.com/api/?name=${encodeURIComponent(text)}&background=ED3B50&color=ffffff&rounded=true&size=128`;

const onSearch = (q: string) => {
  router.get(
    route('categories.parent', props.parent.slug),
    { q },
    { preserveState: true, replace: true }
  );
};


</script>

<template>
  <div class="">
    <!-- Top bar: tiêu đề + ô tìm kiếm -->
    <div class="bg-gray-100 rounded-5 mx-auto my-4 px-2 py-2 ">
      <SearchBar :model-value="filters.q" @update:model-value="onSearch" />
    </div>

    <!-- Nội dung: lặp các danh mục con -->
    <div class=" mx-auto px-4 py-6">
      <div v-if="children.length === 0" class="text-gray-500">Chưa có danh mục con.</div>

      <div class="space-y-10">
        <SubcategoryBlock
          v-for="child in children"
          :key="child.id"
          :title="child.name"
          :items="child.partner_categories.map(pc => ({
            id: pc.id,
            name: pc.name,
            slug: pc.slug,
            image: pc.image || placeholderAvatar(pc.name)
          }))"
        />
      </div>
    </div>
  </div>
</template>
