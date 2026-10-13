export function useSectionList<TItem = any>() {
    const store = useSectionStore()
    const { sectionList } = storeToRefs(store)
    const hasSections = computed(() => !!sectionList.value?.length)
    return {
        /** Список секций */
        sectionList: sectionList as Ref<TItem>,
        /** Есть ли секции */
        hasSections
    }
}