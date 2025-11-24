import { computed, ref, watch } from 'vue';
import { createSearchFilter, type MatchMode } from './search-filter';

type Fetcher<T> = (query: string) => Promise<T>;

interface UseSearchSuggestionOptions<TItem, TResponse> {
    /**
     * Keys used for local filtering when no remote fetcher is provided.
     */
    keys?: string[];
    /**
     * Optional remote fetcher to retrieve suggestions/results.
     */
    fetcher?: Fetcher<TResponse>;
    /**
     * Minimum characters before triggering the fetcher.
     */
    minLength?: number;
    /**
     * Debounce delay for the fetcher (ms).
     */
    debounceMs?: number;
    /**
     * Match mode for local filtering.
     */
    mode?: MatchMode;
    /**
     * Optional selector to extract suggestions from the remote response.
     */
    suggestionSelector?: (response: TResponse) => TItem[];
    /**
     * Optional initial list for local filtering.
     */
    initialItems?: TItem[];
}

export function useSearchSuggestion<TItem, TResponse = TItem[]>({
    keys = [],
    fetcher,
    minLength = 2,
    debounceMs = 250,
    mode = 'AND',
    suggestionSelector,
    initialItems = [],
}: UseSearchSuggestionOptions<TItem, TResponse>) {
    const query = ref('');
    const items = ref<TItem[]>(initialItems);
    const result = ref<TResponse | null>(null);
    const suggestions = ref<TItem[]>(initialItems);
    const loading = ref(false);
    const error = ref<unknown>(null);

    let timer: ReturnType<typeof setTimeout> | null = null;

    const hasQuery = computed(() => query.value.trim().length >= minLength);

    const filteredLocal = computed(() => {
        if (!keys.length || !hasQuery.value) return items.value;
        const filter = createSearchFilter<TItem>(keys, query.value, mode);
        return items.value.filter(filter);
    });

    const clearRemote = () => {
        result.value = null;
        suggestions.value = [];
        error.value = null;
    };

    const runSearch = async (q?: string) => {
        if (!fetcher) return null;
        const term = (q ?? query.value).trim();

        if (term.length < minLength) {
            clearRemote();
            return null;
        }

        loading.value = true;
        error.value = null;

        try {
            const data = await fetcher(term);
            result.value = data;
            if (suggestionSelector) {
                suggestions.value = suggestionSelector(data);
            }
            return data;
        } catch (err) {
            error.value = err;
            return null;
        } finally {
            loading.value = false;
        }
    };

    watch(
        query,
        (val) => {
            if (!fetcher) return;
            if (timer) clearTimeout(timer);

            const trimmed = val.trim();
            if (trimmed.length < minLength) {
                clearRemote();
                return;
            }

            timer = setTimeout(() => runSearch(trimmed), debounceMs);
        },
        { flush: 'post' }
    );

    return {
        query,
        hasQuery,
        items,
        setItems: (next: TItem[]) => (items.value = next),
        filteredLocal,
        result,
        suggestions,
        loading,
        error,
        runSearch,
        clearRemote,
    };
}
