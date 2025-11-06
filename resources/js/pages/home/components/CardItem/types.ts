export interface CardItemProps {
    id: number;
    name: string;
    slug: string;
    image?: string | null;
    description?: string | null;
}

export interface Props {
    cardItem: CardItemProps
    routeHref: string
    showInfo?: boolean
}
