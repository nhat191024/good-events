export interface CardItemProps {
    id: number;
    name: string;
    slug: string;
    image?: string | null;
    image_tag?: string | null;
    description?: string | null;
}

export interface Props {
    cardItem: CardItemProps
    routeHref: string
    showInfo?: boolean
}
