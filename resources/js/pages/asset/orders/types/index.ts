export const AssetOrderStatus = {
    PENDING: 'pending',
    PAID: 'paid',
    CANCELLED: 'cancelled',
} as const;

export type AssetOrderStatus = typeof AssetOrderStatus[keyof typeof AssetOrderStatus];

export interface AssetOrderProductCategory {
    id: number;
    name: string;
    slug: string;
}

export interface AssetOrderProduct {
    id: number;
    name: string;
    slug: string;
    price: number;
    description?: string | null;
    thumbnail?: string | null;
    image_tag?: string | undefined;
    download_zip_url?: string | null;
    category?: AssetOrderProductCategory | null;
}

export interface AssetOrder {
    id: number;
    total: number;
    final_total: number | null;
    status: AssetOrderStatus | string;
    status_label: string;
    created_at: string;
    updated_at: string;
    can_repay: boolean;
    can_download: boolean;
    file_product?: AssetOrderProduct | null;
}

export interface StatusOption {
    value: AssetOrderStatus | string;
    label: string;
}

export type AssetOrdersPayload = Paginated<AssetOrder> | AssetOrder[] | undefined | null;

export interface AssetOrdersPageProps {
    orders?: AssetOrdersPayload;
    activeOrder?: AssetOrder | null;
    statusOptions: StatusOption[];
}
