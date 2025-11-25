import type { Category, Parent, Tag } from '@/pages/home/types';

export interface BlogLocation {
    id: number;
    name: string;
    type?: string | null;
    province?: Parent | null;
}

export interface Author {
    id: number;
    name: string;
}

export interface BlogSummary {
    id: number;
    title: string;
    slug: string;
    type?: string | null;
    video_url?: string | null;
    excerpt?: string | null;
    thumbnail?: string | null;
    published_at?: string | null;
    published_human?: string | null;
    max_people?: number | null;
    location?: BlogLocation | null;
    category?: Category | null;
    author?: Author | null;
    tags?: Tag[] | null;
}

export interface BlogDetail extends BlogSummary {
    content: string;
    read_time_minutes?: number | null;
    address?: string | null;
    latitude?: number | null;
    longitude?: number | null;
}

export interface BlogFilters {
    q?: string | null;
    province_id?: number | string | null;
    district_id?: number | string | null;
    max_people?: number | string | null;
    location_detail?: string | null;
}

export interface BlogDetailContext {
    breadcrumbLabel: string;
    discoverRouteName: string;
    categoryRouteName: string;
    detailRouteName: string;
    pageTitleSuffix?: string | null;
}
