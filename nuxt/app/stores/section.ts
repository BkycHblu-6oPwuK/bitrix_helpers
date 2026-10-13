import { defineStore } from 'pinia'
import type { BreadcrumbItem } from '~/types/breadcrumbs'
import type { FilterDTO, SectionData } from '~/types/iblock/page'
import type { PageData } from '~/types/page'
import type { PaginationDTO } from '~/types/pagination'

export type SectionInitParams = {
    apiUrl: string | URL
    /** true: меняет адресную строку (страница каталога); false: работает без неё (блок на главной) */
    syncUrl?: boolean
}

type LoadOptions = {
    /** дописать товары к списку («Показать ещё») */
    append?: boolean
    /** адрес в строке уже изменён (Назад/Вперёд, ссылка): это и есть публичный адрес, push не нужен */
    restoreTo?: string
}

const SERVICE_PARAMS = ['ajax']

const canon = (raw: string | URL) => {
    const u = new URL(raw.toString(), 'http://localhost')
    SERVICE_PARAMS.forEach(k => u.searchParams.delete(k))
    u.searchParams.sort()
    let p = u.pathname
    try { p = decodeURI(p) } catch { /* как есть */ }
    return `${p.replace(/\/+$/, '')}?${u.searchParams}`
}

const withoutService = (u: URL) => {
    const c = new URL(u.toString())
    SERVICE_PARAMS.forEach(k => c.searchParams.delete(k))
    return c.toString()
}

export const useSectionStore = defineStore('section', () => {
    const router = useRouter()

    const filterData = ref<FilterDTO | null>(null)
    const items = ref<any[]>([])
    const pagination = ref<PaginationDTO | null>(null)
    const path = ref<BreadcrumbItem[] | null>(null)
    const sectionList = ref<any[] | null>(null)
    const additionalData = ref<Record<string, unknown>>({})
    const seo = ref<PageData<SectionData>['seo'] | null>(null)
    const isLoading = ref(false)
    const error = ref<Error | null>(null)

    const syncUrl = ref(false)
    const apiUrl = ref<string | null>(null)
    /** Путь к текущему разделу */
    const sectionPath = ref<string | null>(null)
    /** URL запроса в GET-форме применённого состояния */
    const requestUrl = ref<string | null>(null)
    let origin = ''
    /** Публичный адрес, которому соответствуют данные */
    let publicKey: string | null = null
    /** Соответствие публичного адреса и запроса */
    const requestByPublic = new Map<string, string>()  // публичный → запрос, для Назад/Вперёд
    /** Флаг, указывающий, были ли добавлены элементы к списку (для "Показать ещё") */
    let listAppended = false
    /** Последовательный номер запроса, увеличивается при каждом новом запросе */
    let requestSeq = 0
    /** Базовый URL для построения ссылок с фильтрами и пагинацией, без служебных параметров. */
    const baseUrl = computed(() => {
        if (!apiUrl.value) return null
        if (!syncUrl.value) return apiUrl.value
        const u = new URL(apiUrl.value)
        if(filterData.value?.sectionUrl) {
            u.pathname = filterData.value.sectionUrl
        } else if (sectionPath.value) {
            u.pathname = sectionPath.value
        }
        u.search = ''
        return u.toString()
    })

    const filters = useSectionFilters(filterData, pagination, baseUrl)
    const pager = useSectionPagination(pagination)

    /** Вывод текущего URL без служебных параметров */
    const deriveRequestUrl = () => withoutService(filters.buildPageUrl(pager.currentPage.value))

    /** Применение данных страницы к состоянию */
    const applyPage = (page: SectionData, source: URL) => {
        const { filter, section, sectionList: list, ...additional } = page
        items.value = section?.items ?? []
        pagination.value = section?.pagination ?? null
        path.value = section?.path ?? null
        sectionList.value = list ?? null
        filterData.value = filter ?? null
        additionalData.value = additional
        sectionPath.value = source.pathname
        filters.initializeFiltersFromChecked()
        requestUrl.value = deriveRequestUrl()
    }

    /** Инициализация состояния страницы */
    const initialize = (data: SectionData, params: SectionInitParams) => {
        origin = useRequestURL().origin
        syncUrl.value = !!params.syncUrl

        const s = params.apiUrl.toString()
        apiUrl.value = new URL(s.startsWith('/') || /^https?:/.test(s) ? s : `/${s}`, origin).toString()

        applyPage(JSON.parse(JSON.stringify(data)), new URL(apiUrl.value))

        publicKey = canon(apiUrl.value)
        requestByPublic.clear()
        requestByPublic.set(publicKey, requestUrl.value!)
        listAppended = false
        requestSeq++
        isLoading.value = false
    }

    /** Преобразование запроса в публичный URL */
    const toPublicUrl = (filterUrl?: string | null) => {
        const state = filters.buildPageUrl(pager.currentPage.value)
        const target = filterUrl ? new URL(filterUrl, origin) : new URL(state.pathname, origin)

        if (filterUrl) {
            const keep = [filterData.value?.sorting?.requestParam || 'sort', pagination.value?.paginationUrlParam]
            keep.forEach(k => {
                if (!k) return
                if (state.searchParams.has(k)) target.searchParams.set(k, state.searchParams.get(k)!)
                else target.searchParams.delete(k)
            })
        } else {
            target.search = state.search
        }
        SERVICE_PARAMS.forEach(k => target.searchParams.delete(k))
        return target.pathname + target.search
    }

    /** Загрузка страницы */
    const loadPage = async (url: URL, { append = false, restoreTo }: LoadOptions = {}) => {
        const reqUrl = new URL(url)
        reqUrl.searchParams.set('ajax', 'y')

        const seq = ++requestSeq
        isLoading.value = true
        error.value = null

        try {
            const res = await useApiFetch<PageData<SectionData>>(reqUrl.pathname, {
                query: Object.fromEntries(reqUrl.searchParams),
            })
            if (seq !== requestSeq) return
            const page = res?.data?.page
            if (!page) throw new Error('Invalid response format')

            if (append) {
                items.value = [...items.value, ...(page.section?.items ?? [])]
                pagination.value = page.section?.pagination ?? null
                requestUrl.value = deriveRequestUrl()
            } else {
                applyPage(page, reqUrl)
            }
            listAppended = append
            if (res.data.seo) seo.value = res.data.seo

            if (syncUrl.value) {
                const filterUrl = Object.keys(filters.appliedFilters.value).length ? page.filter?.filterUrl : null
                const target = restoreTo ?? toPublicUrl(filterUrl)

                publicKey = canon(target)
                requestByPublic.set(publicKey, requestUrl.value!)

                if (!restoreTo && import.meta.client && canon(router.currentRoute.value.fullPath) !== publicKey) {
                    await router.push(target)
                }
            }
        } catch (e) {
            if (seq === requestSeq) error.value = e as Error
        } finally {
            if (seq === requestSeq) isLoading.value = false
        }
    }

    /** Назад/Вперёд и внешние переходы внутри каталога; собственные push пропускаются */
    const syncFromRoute = (fullPath: string) => {
        if (!syncUrl.value || canon(fullPath) === publicKey) return
        const req = requestByPublic.get(canon(fullPath)) ?? fullPath
        return loadPage(new URL(req, origin), { restoreTo: fullPath })
    }

    /** запрос только если фильтры или сортировка реально изменились */
    const applyFilters = async () => {
        if (filters.isDirty.value) await loadPage(filters.buildFilterUrl())
    }
    /** Сброс фильтров к исходному состоянию */
    const resetFilters = () => { filters.clearFilters(); return applyFilters() }
    /** Применение сортировки */
    const applySorting = (sortId: string) => { filters.setSorting(sortId); return applyFilters() }

    /** та же страница не перезагружается, кроме случая, когда список дописан «Показать ещё» */
    const goToPage = async (page: number) => {
        if (page === pager.currentPage.value && !listAppended) return
        await loadPage(filters.buildPageUrl(page))
    }
    /** Обработчик показать ещё */
    const showMore = async () => {
        if (!pager.hasMore.value) return
        await loadPage(filters.buildPageUrl(pager.currentPage.value + 1), { append: true })
    }

    return {
        filterData,
        items,
        pagination,
        path,
        sectionList,
        additionalData,
        seo,
        isLoading,
        error,
        selectedFilters: filters.selectedFilters,
        currentSortId: filters.currentSortId,
        hasActiveFilters: filters.hasActiveFilters,
        selectedFilterCount: filters.selectedFilterCount,
        isDirty: filters.isDirty,
        toggleFilter: filters.toggleFilter,
        initialize,
        syncFromRoute,
        applyFilters,
        resetFilters,
        applySorting,
        goToPage,
        showMore,
        ...pager,
    }
})