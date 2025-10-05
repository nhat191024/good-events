import { AppPageProps } from '@/types/index';
import type { Page as InertiaPage, PageProps as InertiaPageProps, Router } from '@inertiajs/core'
import type { createHeadManager } from '@inertiajs/vue3'

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps { }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}

declare global {
    type Paginated<T> = {
        data: T[]
        meta: {
            current_page: number
            last_page: number
            per_page: number
            total: number
            [k: string]: unknown
        }
        links?: {
            next: string
        }
    }
}

export { }
