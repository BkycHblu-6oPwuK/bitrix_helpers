import type { SearchProductDTO } from '~/types/iblock/search'

export function useSearchSuggest() {
    const store = useSearchStore()

    const isOpen = ref(false)
    const isEmpty = computed(() => store.suggestQuery.trim().length === 0)
    const query = computed({ get: () => store.suggestQuery, set: (v) => store.setQuery(v) })
    const isOpenComputed = computed(() => isOpen.value)
    const isLoading = computed(() => store.suggestLoading)
    const tags = computed(() => store.tags)
    const history = computed(() => store.history)
    const isEmptyResult = computed(() =>
        !isEmpty.value &&
        store.hasSearched &&
        !store.suggestLoading &&
        store.suggestProducts.length === 0
    )
    const displayProducts = computed<SearchProductDTO[]>(() =>
        isEmpty.value ? store.initProducts : store.suggestProducts
    )
    const hasHistory  = computed(() => isEmpty.value && store.history.length > 0)
    const hasTags     = computed(() => isEmpty.value && store.tags.length > 0)
    const hasProducts = computed(() => displayProducts.value.length > 0)

    const open = () => {
        if (isOpen.value) return
        isOpen.value = true
        store.loadHistory()
        store.loadInit()
    }
    const close = () => isOpen.value = false;

    return {
        query,
        isOpen: isOpenComputed,
        isLoading,
        isEmpty,
        isEmptyResult,
        displayProducts,
        hasHistory,
        hasTags,
        hasProducts,
        tags,
        history,
        open: open,
        close: close,
        pickQuery: store.pickQuery,
        commitToHistory: store.addToHistory,
        removeFromHistory: store.removeFromHistory,
        clearHistory: store.clearHistory,
    }
}
