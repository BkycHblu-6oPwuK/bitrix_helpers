const HISTORY_KEY = 'search_history'
const HISTORY_MAX = 10

export function useSearchHistory() {
    const history = ref<string[]>([])

    const readHistory = (): string[] => {
        if (!import.meta.client) return []
        try {
            return JSON.parse(localStorage.getItem(HISTORY_KEY) ?? '[]') as string[]
        } catch {
            return []
        }
    }

    const writeHistory = (items: string[]) => {
        if (!import.meta.client) return
        localStorage.setItem(HISTORY_KEY, JSON.stringify(items.slice(0, HISTORY_MAX)))
    }

    const loadHistory = () => {
        history.value = readHistory()
    }

    const addToHistory = (value: string) => {
        const trimmed = value.trim()
        if (!trimmed) return
        const items = readHistory().filter(i => i !== trimmed)
        items.unshift(trimmed)
        history.value = items.slice(0, HISTORY_MAX)
        writeHistory(history.value)
    }

    const removeFromHistory = (value: string) => {
        history.value = history.value.filter(i => i !== value)
        writeHistory(history.value)
    }

    const clearHistory = () => {
        history.value = []
        writeHistory([])
    }

    return {
        history,
        loadHistory,
        addToHistory,
        removeFromHistory,
        clearHistory,
        readHistory,
        writeHistory,
    }
}
