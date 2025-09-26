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
