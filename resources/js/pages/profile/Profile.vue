<script setup lang="ts">
import { computed } from 'vue'
import ClientProfile from './client/Client.vue'
import PartnerProfile from './partner/Partner.vue'

type ProfileType = 'client' | 'partner'

type ClientPayload = {
    user: {
        id: number
        name: string
        avatar_url: string
        email: string
        phone: string
        created_year: string | null
        location: string | null
        partner_profile_name?: string | null
        bio?: string | null
        is_verified?: boolean
        email_verified_at?: string | null
    }
    stats: {
        orders_placed: number
        completed_orders: number
        cancelled_orders_pct: string
        total_spent: number
        last_active_human: string | null
        avg_rating: number | null
    }
    recent_bills: Array<{
        id: number
        code: string
        status: string
        final_total: number | null
        date: string | null
        event: string | null
        category: string | null
        partner_name: string | null
    }>
    recent_reviews: Array<{
        id: number
        subject_name: string
        department: string
        review: string | null
        overall: number | null
        created_human: string | null
    }>
    intro: string | null
}

type PartnerPayload = {
    user: {
        id: number; name: string; avatar_url: string; location: string | null;
        joined_year: string | null; is_pro: boolean; rating: number; total_reviews: number; total_customers: number | null;
        bio?: string | null; is_verified?: boolean; email_verified_at?: string | null;
    }
    stats: { customers: number; years: number; satisfaction_pct: string; avg_response: string }
    quick: { orders_placed: number; completed_orders: number; cancelled_orders_pct: string; last_active_human: string | null }
    contact: { phone: string | null; email: string | null; response_time: string; languages: string[]; timezone: string }
    services: Array<{ id: number; name: string | null; field: string | null; price: number | null; media: Array<{ id: number; url: string }> }>
    reviews: Array<{ id: number; author: string; rating: number; review: string | null; created_human: string | null }>
    intro: string | null
    video_url: string | null
}

const props = defineProps<{
    profile_type: ProfileType
    payload: ClientPayload | PartnerPayload
}>()

const isPartner = computed(() => props.profile_type === 'partner')
const clientPayload = computed(() => props.payload as ClientPayload)
const partnerPayload = computed(() => props.payload as PartnerPayload)
</script>

<template>
    <ClientProfile v-if="!isPartner" v-bind="clientPayload" />
    <PartnerProfile v-else v-bind="partnerPayload" />
</template>
