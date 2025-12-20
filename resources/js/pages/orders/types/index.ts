import { Event, Metrics } from '@/types/database'

export interface OrderVoucher {
    id: number
    code: string
}

export interface ClientOrderDetail {
    id: number
    total: number | null
    status: OrderDetailStatus
    created_at: string | null
    updated_at: string | null
    partner?: Partner | null
}

export interface ClientOrder {
    id: number
    code: string
    address: string
    date: string
    start_time: string
    end_time: string
    total: number | null
    arrival_photo: string | null
    final_total: number | null
    note: string
    status: OrderStatus
    thread_id: number
    voucher?: OrderVoucher | null
    created_at: string
    updated_at: string
    category: Category
    custom_event: string | null
    event: Pick<Event, 'name'> | null
    partners: { count: number } | null
    partner: Partner | null
    review: Review | null
}

export interface Review {
    rating: number,
    comment: string,
    recommend: true,
}

export interface ClientOrderHistory {
    id: number
    code: string
    address: string
    date: string
    start_time: string
    end_time: string
    total: number | null
    final_total: number | null
    note: string
    status: OrderStatus
    voucher?: OrderVoucher | null
    created_at: string
    updated_at: string
    category: Category
    event: Pick<Event, 'name'> | null
    partner: Partner | null
    review: Review | null
}

export const OrderStatus = {
    PENDING: 'pending',
    CONFIRMED: 'confirmed',
    COMPLETED: 'completed',
    CANCELLED: 'cancelled',
    IN_JOB: 'in_job',
    EXPIRED: 'expired'
} as const;

export type OrderStatus = typeof OrderStatus[keyof typeof OrderStatus] | string;

export const OrderDetailStatus = {
    NEW: 'new',
    CLOSED: 'closed'
} as const;

export type OrderDetailStatus = typeof OrderDetailStatus[keyof typeof OrderDetailStatus] | string;

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
