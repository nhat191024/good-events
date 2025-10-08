import { Event, Metrics } from '@/types/database'

export interface ClientOrderDetail {
    id: number
    total: number | null
    status: OrderDetailStatus
    created_at: string
    updated_at: string
    partner?: Partner | null
}

export interface ClientOrder {
    id: number
    code: string
    address: string
    date: string
    start_time: string
    end_time: string
    final_total: number | null
    note: string
    status: OrderStatus
    created_at: string
    updated_at: string
    category: Category
    event: Pick<Event, 'name'>
    partners: { count: number }
}

export interface ClientOrderHistory {
    id: number
    code: string
    address: string
    date: string
    start_time: string
    end_time: string
    final_total: number | null
    note: string
    status: OrderStatus
    created_at: string
    updated_at: string
    category: Category
    event: Pick<Event, 'name'>
    partner: Partner
}

export type OrderStatus = 'pending' | 'confirmed' | 'completed' | 'cancelled' | 'expired' | string
export type OrderDetailStatus = 'new' | 'closed' | string

export interface Category {
    id: number
    name: string
    max_price: number
    min_price: number
    parent: ParentCategory
    image?: string
}

export interface ParentCategory {
    id: number
    name: string
}

export interface PartnerProfile {
    id: number
    partner_name: string
}

// or, this is actually User table in the backend
export interface Partner {
    id: number
    name: string
    avatar: string
    partner_profile?: PartnerProfile
    statistics: Pick<Metrics,
    | 'total_ratings'
    | 'average_stars'
    // | 'some_metric'
    // | 'some_metric'

    // add more metrics here before modifying on the vue components
    >
}

export type OrderDetailsPayload =
    | { billId: number; items: ClientOrderDetail[]; version?: number }
    | { billId: number; items: { data: ClientOrderDetail[] }; version?: number }
    | null

export type ClientOrderHistoryPayload = { data: ClientOrderHistory[]; meta?: any; links?: any }
export type ClientOrderPayload = { data: ClientOrder[]; meta?: any; links?: any }
export type SingleClientOrderPayload = { data: ClientOrder; meta?: any; links?: any }
