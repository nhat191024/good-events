import type { Category, FileProduct } from '@/pages/home/types';
import type { InertiaForm } from '@inertiajs/vue3';

export interface PaymentMethod {
    code: string;
    name: string;
    description?: string | null;
}

export interface BuyerInfo {
    name?: string | null;
    email?: string | null;
    phone?: string | null;
    company?: string | null;
    tax_code?: string | null;
    note?: string | null;
    payment_method?: string | null;
}

export interface Totals {
    subtotal?: number | null;
    discount?: number | null;
    vat?: number | null;
    total?: number | null;
}

export interface PurchaseFormFields {
    slug: string;
    name: string;
    email: string;
    phone: string;
    company: string;
    tax_code: string;
    note: string;
    payment_method: string;
}

export type PurchaseForm = InertiaForm<PurchaseFormFields>;

export interface PurchasePageProps {
    fileProduct: FileProduct & { category?: Category | null };
    buyer?: BuyerInfo | null;
    paymentMethods?: PaymentMethod[];
    totals?: Totals | null;
}
