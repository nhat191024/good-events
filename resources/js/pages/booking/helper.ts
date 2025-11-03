/** Get img or fallback
 * @param media string
 * @returns
 */
export function getImg(media: string | undefined | null): string {
    const placeHolder = 'https://ui-avatars.com/api/?name=N+A&background=random&size=16';
    return media || placeHolder;
}
