export type ContentBlockType = 'paragraph' | 'list' | 'subheading';

export interface ContentBlock {
    type: ContentBlockType;
    text?: string;
    title?: string | null;
    items?: string[];
}

export interface StaticSection {
    id: string;
    title: string;
    summary?: string | null;
    blocks: ContentBlock[];
}

export interface StaticPagePayload {
    slug: string;
    title: string;
    intro?: string | null;
    hero?: {
        kicker?: string | null;
        note?: string | null;
    };
    sections: StaticSection[];
}
