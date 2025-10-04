import { Media } from "@/types/database";

export function getFirstImg(media: Media[] | undefined) : string {
    return media?.[0]?.original_url || '';
}
