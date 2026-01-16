export interface Event {
    id: number
    name: string
    deleted_at: string | null
    created_at: string
    updated_at: string
}

export interface Location {
    id: number;
    name: string;
    code: string;
    codename: string;
    short_codename?: string | null;
    type: string;
    phone_code?: string | null;
    parent_id?: number | null;
    created_at: string;
    updated_at: string;
}

export interface PartnerCategory {
    id: number
    name: string
    slug: string
    parent_id: number | null
    min_price: number | null
    max_price: number | null
    description: string
    deleted_at: string | null
    created_at: string
    updated_at: string
    media?: string
    image_tag?: string
}

export interface PartnerBill {
    id: number
    code: string
    address: string
    phone: string
    date: string | null
    start_time: string | null
    end_time: string | null
    final_total: number | null
    event_id: number | null
    client_id: number | null
    partner_id: number | null
    category_id: number | null
    note: string | null
    status: string
    thread_id: number | null
    created_at: string
    updated_at: string
}

export interface Ward {
    id: number,
    name: string
}

export interface WardTypeSelectBox {
    value?: string,
    name: string
}

export interface Province {
    id: number,
    name: string
}

export interface Metrics {
    number_customer: string
    satisfaction_rate: string
    orders_placed: string
    completed_orders: string
    cancelled_orders_percentage: string
    total_spent: string
    average_stars: string
    total_ratings: string
}

// export interface Review {

// }

