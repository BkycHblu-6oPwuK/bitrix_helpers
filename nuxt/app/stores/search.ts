import type { SearchInitDTO, SearchSuggestDTO, SearchProductDTO, SearchTagDTO } from '~/types/iblock/search'

export const useSearchStore = defineStore('search', () => {
    const tags = ref<SearchTagDTO[]>([])
    const initProducts = ref<SearchProductDTO[]>([])
    const initialized = ref(false)
    const loading = ref(false)
    const suggestQuery = ref('')
    const suggestProducts = ref<SearchProductDTO[]>([])
    const suggestLoading = ref(false)
    const hasSearched = ref(false)
    const { history, loadHistory, addToHistory, removeFromHistory, clearHistory, readHistory, writeHistory } = useSearchHistory()

    const loadInit = async () => {
        if (initialized.value || loading.value) return
        loading.value = true
        try {
            const { data } = await useApiFetch<SearchInitDTO>('search/init')
            if (data?.tags) {
                tags.value = data.tags ?? []
                initProducts.value = data.products ?? []
                initialized.value = true
            }
        } catch (e) {
            console.error('[useSearchStore] init error', e)
        } finally {
            loading.value = false
        }
    }

    const fetchSuggest = async (q: string) => {
        hasSearched.value = false
        try {
            const { data } = await useApiFetch<SearchSuggestDTO>(
                `search/suggest/${encodeURIComponent(q)}`
            )
            suggestProducts.value = data?.products ?? []
        } catch (e) {
            console.error('[useSearchStore] suggest error', e)
            suggestProducts.value = []
        } finally {
            suggestLoading.value = false
            hasSearched.value = true
        }
    }

    const debouncedFetch = debounce((q: string) => fetchSuggest(q), 300)

    const setQuery = (value: string) => {
        suggestQuery.value = value
        suggestLoading.value = true

        if (!value.trim()) {
            suggestProducts.value = []
            suggestLoading.value = false
            hasSearched.value = false
            return
        }

        debouncedFetch(value.trim())
    }

    const pickQuery = (value: string) => {
        suggestQuery.value = value
        fetchSuggest(value)
    }

    return {
        tags,
        initProducts,
        initialized,
        loading,
        history,
        suggestQuery,
        suggestProducts,
        suggestLoading,
        hasSearched,

        loadInit,
        fetchSuggest,
        setQuery,
        pickQuery,
        loadHistory,
        addToHistory,
        removeFromHistory,
        clearHistory,
        readHistory,
        writeHistory,
    }
})
