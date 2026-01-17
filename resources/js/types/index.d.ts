import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    avatar_url?: string | null;
    avatar_image_tag?: string | null;
    partner_profile_name?: string | null;
    country_code?: string | null;
    phone?: string | null;
    bio?: string | null;
    is_verified?: boolean;
    location?: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface AppSettings {
    app_name: string;
    app_logo?: string | null;
    app_favicon?: string | null;
    contact_hotline?: string | null;
    contact_email?: string | null;
    social_facebook?: string | null;
    social_facebook_group?: string | null;
    social_zalo?: string | null;
    social_youtube?: string | null;
    social_tiktok?: string | null;
}

export type BreadcrumbItemType = BreadcrumbItem;
