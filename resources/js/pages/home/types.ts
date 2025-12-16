import { CardItemProps } from "./components/CardItem/types"

export interface Category {
    id: number
    name: string
    slug: string
    description: string
    parent: Parent | null
    image?: string
}

export interface Parent {
    id: number
    name: string
}

export interface FileProduct {
    id: number
    name: string
    slug: string
    description: string
    price: number
    image?: string
    created_at: string
    updated_at: string
    category: Category
}

export interface Tag {
    id: number
    name: string
    slug: string
}

export interface AssetCardItemProps {
    id: number;
    name: string;
    slug: string;
    image?: string | null;
    description?: string | null;
    category: Category // additional field
}

export interface RentProduct extends FileProduct {}

export type RentCardItemProps = AssetCardItemProps;
