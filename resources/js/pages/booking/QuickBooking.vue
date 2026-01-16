<script setup lang="ts">
    import { usePage, Link, Head } from '@inertiajs/vue3';
    import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
    import SelectPartnerHeader from '@/pages/booking/layout/Header.vue'
    import LargeSearchBar from '@/components/LargeSearchBar.vue'
    import CardItem from '@/pages/booking/components/CardItem.vue'
    import { PartnerCategory as PartnerCategoryType } from '@/types/database';
    import CardGrid from '@/pages/booking/layout/CardGrid.vue';
    import { computed, ref, watch } from 'vue';
    import { createSearchFilter } from '@/lib/search-filter'
    import { getImg } from './helper';
    import { useTutorialHelper } from '@/lib/tutorial-helper';
    import { tutorialQuickLinks } from '@/lib/tutorial-links';
    import { inject } from "vue";

    const route = inject('route') as any;

    const title: string = 'Bạn đang cần kiểu đối tác nào cho sự kiện?'
    const subtitle: string = 'Chọn loại sụ kiện phù hợp với nhu cầu của bạn'

    const pageProps = usePage().props
    const partnerCategories = computed(() => pageProps.partnerCategories as PartnerCategoryType[])

    const searchKeyword = ref('')
    const searchColumns = ['name', 'slug', 'description']
    const filteredPartnerCategories = computed(() => {
        const filter = createSearchFilter(searchColumns, searchKeyword.value)
        return partnerCategories.value.filter(filter)
    })

    const { addTutorialRoutes } = useTutorialHelper();

    addTutorialRoutes([
        tutorialQuickLinks.clientQuickOrder,
    ]);
</script>

<!-- quick booking page STEP 1-->
<template>
    <Head title="Đặt show nhanh" />
    <!-- layout -->
    <ClientAppHeaderLayout :show-banner-background=true :background-class-names="'bg-white'">
        <SelectPartnerHeader :title="title" :subtitle="subtitle" :icon-name="'partyPopper'">
            <!-- search bar -->
            <div class="w-full relative">
                <LargeSearchBar v-model="searchKeyword" :placeholder="'Tìm loại đối tác...'"/>
            </div>
            <!-- grid list -->
            <CardGrid>
                <Link v-for="item in filteredPartnerCategories"
                    :href="route('quick-booking.choose-partner-category', { partner_category_slug: item.slug })">
                    <CardItem :title="item.name" :description="item.description??''" :card-img-src="getImg(item.media)" :card-img-tag="item.image_tag" />
                </Link>
            </CardGrid>
        </SelectPartnerHeader>
    </ClientAppHeaderLayout>
</template>
