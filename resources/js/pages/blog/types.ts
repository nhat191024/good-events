import type { Category, Tag } from '@/pages/home/types';

export interface Author {
    id: number;
    name: string;
}

export interface BlogSummary {
    id: number;
    title: string;
    slug: string;
    excerpt?: string | null;
    thumbnail?: string | null;
    published_at?: string | null;
    published_human?: string | null;
    category?: Category | null;
    author?: Author | null;
    tags?: Tag[] | null;
}

export interface BlogDetail extends BlogSummary {
    content: string;
    read_time_minutes?: number | null;
}

export interface BlogFilters {
    q?: string | null;
}

