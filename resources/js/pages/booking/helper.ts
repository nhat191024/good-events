type HasMediaUrl = { original_url?: string, url?: string, preview_url?: string }

/** Get img from the first media object in the list[]
 ** How to use in components: getFirstImg(props.category?.media)
 * @param media Media[]
 * @returns
 */
export function getFirstImg(media: HasMediaUrl[] | undefined): string {
    const placeHolder = 'https://ui-avatars.com/api/?name=N+A&background=random&size=16';
    return media?.[0]?.preview_url || media?.[0]?.url || media?.[0]?.original_url || placeHolder;
}
