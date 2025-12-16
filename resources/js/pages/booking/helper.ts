/** Get img or fallback
 * @param media string
 * @returns
 */
export function getImg(media: string | undefined | null): string {
    const placeHolder = 'https://ui-avatars.com/api/?name=N+A&background=random&size=16'

    if (!media || media === 'null' || media === 'undefined' || media.trim() === '') {
        return placeHolder
    }

    const hasExtension = /\.[a-zA-Z0-9]+($|\?.*)/.test(media)

    if (!hasExtension) {
        return placeHolder
    }

    return media
}

