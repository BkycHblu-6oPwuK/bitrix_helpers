export function useSectionList<TItem = any>() {
    const section = useSectionPage()
    const { sectionList } = toRefs(section)
    const hasSections = computed(() => !!sectionList.value?.length)
    return {
        /** Список секций */
        sectionList: sectionList as Ref<TItem>,
        /** Есть ли секции */
        hasSections
    }
}