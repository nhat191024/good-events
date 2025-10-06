<script setup lang="ts">
import { ref, computed } from 'vue';
import SearchBar from './partials/SearchBar.vue';
import SubcategoryBlock from './partials/SubcategoryBlock.vue';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

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

// Local client-side search state
const q = ref(props.filters?.q ?? '');

// Filter children and their partner categories by name
const filteredChildren = computed(() => {
  const term = q.value.trim().toLowerCase();
  if (!term) return props.children;
  return props.children
    .map((child) => ({
      ...child,
      partner_categories: child.partner_categories.filter((pc) =>
        pc.name.toLowerCase().includes(term)
      ),
    }))
    .filter((child) => child.partner_categories.length > 0);
});


</script>

<template>
  <ClientHeaderLayout>
    <!-- Top bar: tiêu đề + ô tìm kiếm -->
    <div class="bg-gray-100 rounded-5 mx-auto my-4 px-2 py-2 w-full md:w-[80%] ">
      <SearchBar v-model="q" />
    </div>

    <!-- Nội dung: lặp các danh mục con -->
    <div class=" mx-auto px-4 py-6">
      <div v-if="children.length === 0" class="text-gray-500">Chưa có danh mục con.</div>

      <div class="space-y-10">
        <SubcategoryBlock
          v-for="child in filteredChildren"
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
  </ClientHeaderLayout>
</template>

