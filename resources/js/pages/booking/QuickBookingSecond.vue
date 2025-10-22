<script setup lang="ts">
    import { usePage, Link, Head } from '@inertiajs/vue3';
    import ClientAppHeaderLayout from  '@/layouts/app/ClientHeaderLayout.vue'
    import SelectPartnerHeader from  '@/pages/booking/layout/Header.vue'
    import LargeSearchBar from '@/components/LargeSearchBar.vue'
    import CardItem from '@/pages/booking/components/CardItem.vue'
    import FormGroupLayout from '@/pages/booking/layout/FormGroup.vue'
    import Button from '@/components/ui/button/Button.vue'
    import { PartnerCategory } from '@/types/database';
    import CardGrid from './layout/CardGrid.vue';
    import { createSearchFilter } from '@/lib/search-filter';
    import { computed, ref } from 'vue';
    import { getImg } from './helper';

    const pageProps = usePage().props
    const partnerChildrenList = computed(() => pageProps.partnerChildrenList as PartnerCategory[])
    // parent
    const partnerCategory = pageProps.partnerCategory as PartnerCategory
    const parentPartnerCategorySlug = partnerCategory.slug as string

    const title = `Trong lĩnh vực \'${partnerCategory.name}\', bạn muốn thuê đối tác cụ thể nào dưới đây?`
    const subtitle = 'Chọn loại sụ kiện quay chụp phù hợp với nhu cầu'

    // TODO: test img, needs to change later
    const cardImgDemo = "https://framerusercontent.com/images/IDBlVR9F6tbH9i8opwaJiutM.png?scale-down-to=512&width=1024&height=1024"

    const searchKeyword = ref('')
    const searchColumns = ['name', 'slug', 'description']
    const filteredPartnerChildrenList = computed(() => {
        const filter = createSearchFilter(searchColumns, searchKeyword.value)
        return partnerChildrenList.value.filter(filter)
    })
</script>

<!-- quick booking page STEP 2 -->
<template>
    <Head title="Đặt show nhanh - Điền thông tin" />
    <!-- layout -->
    <ClientAppHeaderLayout>
        <SelectPartnerHeader :title="title" :subtitle="subtitle" :header-img-src="getImg(partnerCategory.media)">
            <!-- search bar -->
            <div class="w-full relative">
                <LargeSearchBar v-model="searchKeyword" :placeholder="'Tìm cụ thể đối tác...'" />
            </div>
            <FormGroupLayout class="mb-3 justify-center">
                <Link :class="'w-52 items-center flex flex-col'" :href="route('quick-booking.choose-category')">
                    <Button :variant="'outlineWhite'" :size="'lg'" :class="'w-full cursor-pointer'">Quay lại</Button>
                </Link>
            </FormGroupLayout>
            <!-- grid list -->
            <CardGrid>
                <Link v-for="item in filteredPartnerChildrenList" :href="route('quick-booking.fill-info',{partner_child_category_slug: item.slug, partner_category_slug: parentPartnerCategorySlug})">
                    <CardItem :title="item.name" :description="item.description??''" :card-img-src="getImg(item.media)"/>
                </Link>
            </CardGrid>
        </SelectPartnerHeader>
    </ClientAppHeaderLayout>
</template>
