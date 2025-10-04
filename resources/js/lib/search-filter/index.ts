//! USAGE EXAMPLE
//? how to use the search filter helper
//* const searchKeyword = ref('')
//* const partnerCategories = computed(() => pageProps.partnerCategories as PartnerCategoryType[])
//* const searchColumns = ['name', 'slug', 'description', 'min_price', 'max_price']
//* const filteredPartnerCategories = computed(() => {
//*     const filter = createSearchFilter(searchColumns, searchKeyword.value)
//*     return partnerCategories.value.filter(filter)
//* })

export type MatchMode = 'AND' | 'OR'

export function normText(input?: unknown): string {
    const s = (input ?? '').toString()
    // decomposed + remove diacritics (unicode range)
    return s
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

export function getNested(obj: any, path: string) {
    return path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj)
}

export function tokenize(q?: string): string[] {
    return normText(q)
        .split(/\s+/)
        .filter(Boolean)
}

export function buildHaystack(item: any, keys: string[]) {
    const parts: string[] = []
    for (const k of keys) {
        const v = getNested(item, k)
        if (Array.isArray(v)) parts.push(v.join(' '))
        else if (v != null) parts.push(String(v))
    }
    return normText(parts.join(' '))
}

export function matchTokens(haystack: string, tokens: string[], mode: MatchMode = 'AND') {
    if (tokens.length === 0) return true
    if (mode === 'AND') return tokens.every(t => haystack.includes(t))
    return tokens.some(t => haystack.includes(t))
}

export function createSearchFilter<T = any>(keys: string[], query?: string, mode: MatchMode = 'AND') {
    const tokens = tokenize(query)
    return (item: T) => {
        const hay = buildHaystack(item, keys)
        return matchTokens(hay, tokens, mode)
    }
}

export function paginate<T>(arr: T[], page = 1, perPage = 20) {
    const start = (page - 1) * perPage
    return arr.slice(start, start + perPage)
}

export function highlight(text: string, tokens: string[]) {
    if (!tokens.length) return text
    let out = text
    for (const t of tokens) {
        if (!t) continue
        // escape token for regex (simple)
        const re = new RegExp(t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi')
        out = out.replace(re, (m) => `<mark>${m}</mark>`)
    }
    return out
}
