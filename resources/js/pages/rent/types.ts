import type {
    Category as BaseCategory,
    Tag as BaseTag,
    RentProduct as BaseRentProduct,
} from '@/pages/home/types';

export type Category = BaseCategory;
export type Tag = BaseTag;

export interface PreviewMedia {
    id?: number | string | null;
    url: string;
    thumbnail?: string | null;
    alt?: string | null;
}

export interface IncludedFile {
    id?: number | string | null;
    name: string;
    size?: string | null;
    format?: string | null;
    pages?: number | null;
    download_url?: string | null;
}

export interface RentProduct extends BaseRentProduct {
    long_description?: string | null;
    highlights?: string[] | null;
    preview_images?: PreviewMedia[] | string[];
    included_files?: IncludedFile[] | null;
    updated_human?: string | null;
    tags?: Tag[];
}

export interface DiscoverFiltersPayload {
    q?: string | null;
    tags?: string[] | null;
    tag?: string | null;
}
