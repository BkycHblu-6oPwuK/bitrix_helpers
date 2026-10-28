import type { InjectionKey, UnwrapNestedRefs } from 'vue'
import type { RouteLocationNormalized } from 'vue-router'
import type { FilterDTO, SectionData } from '~/types/iblock/page'
import type { PageData } from '~/types/page'
import type { PaginationDTO } from '~/types/pagination'

export type SectionInitParams = {
    apiUrl: string | URL
    /** true: меняет адресную строку (страница каталога); false: работает без неё (блок на главной) */
    syncUrl?: boolean
    /** Включать номер страницы в URL */
    pageInUrl?: boolean
    /** Дополнительные служебные параметры для исключения из публичного URL */
    serviceParams?: string[]
    /** Логика для остановки синхронизации URL при смене маршрута */
    onStopSyncUrl?: (to: RouteLocationNormalized, from: RouteLocationNormalized) => boolean
}

/**
 * Контекст возвращается как reactive-объект, как раньше стор:
 * `section.items` работает без `.value`, а для деструктуризации используйте `toRefs(section)`.
 */
export type SectionPageContext = UnwrapNestedRefs<ReturnType<typeof createSectionPage>>

type LoadOptions = {
    /** дописать товары к списку («Показать ещё») */
    append?: boolean
    /** адрес в строке уже изменён (Назад/Вперёд, ссылка): это и есть публичный адрес, push не нужен */
    restoreTo?: string
}

const sectionPageKey: InjectionKey<SectionPageContext> = Symbol('sectionPage')
const BASE_SERVICE_PARAMS = ['ajax']

/** Канонический вид адреса для сравнения: без служебных параметров, отсортированный query, без хвостового / */
const canon = (raw: string | URL, serviceParams: string[]) => {
    const u = new URL(raw.toString(), 'http://localhost')
    serviceParams.forEach(k => u.searchParams.delete(k))
    u.searchParams.sort()
    let p = u.pathname
    try { p = decodeURI(p) } catch { /* как есть */ }
    return `${p.replace(/\/+$/, '')}?${u.searchParams}`
}

const withoutService = (u: URL, serviceParams: string[]) => {
    const c = new URL(u.toString())
    serviceParams.forEach(k => c.searchParams.delete(k))
    return c.toString()
}

/**
 * Состояние раздела/списка: данные, фильтры, пагинация, загрузка, синхронизация с адресной строкой.
 * Всё состояние живёт внутри экземпляра (а не в модуле и не в глобальном сторе),
 * поэтому на одной странице может быть несколько независимых разделов.
 */
function createSectionPage(initialData: SectionData | null | undefined, params: SectionInitParams) {
    const router = useRouter()
    const origin = useRequestURL().origin

    const pageSeo = usePageSeoState()
    const pagePath = usePagePathState()
    const filterData = ref<FilterDTO | null>(null)
    const items = ref<any[]>([])
    const pagination = ref<PaginationDTO | null>(null)
    const sectionList = ref<any[] | null>(null)
    const additionalData = ref<Record<string, unknown>>({})
    const isLoading = ref(false)
    const error = ref<Error | null>(null)

    const syncUrl = ref(false)
    const pageInUrl = ref(true)
    const apiUrl = ref<string | null>(null)
    /** Путь к текущему разделу */
    const sectionPath = ref<string | null>(null)
    /** URL запроса в GET-форме применённого состояния */
    const requestUrl = ref<string | null>(null)

    /** Служебные параметры (у каждого экземпляра свои) */
    let serviceParams = [...BASE_SERVICE_PARAMS]
    /** Публичный адрес, которому соответствуют данные */
    let publicKey: string | null = null
    /** Соответствие публичного адреса и запроса */
    const requestByPublic = new Map<string, string>()  // публичный → запрос, для Назад/Вперёд
    /** Флаг, указывающий, были ли добавлены элементы к списку (для "Показать ещё") */
    let listAppended = false
    /** Последовательный номер запроса, увеличивается при каждом новом запросе */
    let requestSeq = 0

    const toCanon = (raw: string | URL) => canon(raw, serviceParams)

    /** Базовый URL для построения ссылок с фильтрами и пагинацией, без служебных параметров. */
    const baseUrl = computed(() => {
        if (!apiUrl.value) return null
        if (!syncUrl.value) return apiUrl.value
        const u = new URL(apiUrl.value)
        if (filterData.value?.sectionUrl) {
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
    const deriveRequestUrl = () => withoutService(filters.buildPageUrl(pager.currentPage.value), serviceParams)

    /** Применение данных страницы к состоянию */
    const applyPage = (page: SectionData, source: URL) => {
        const { filter, section, sectionList: list, ...additional } = page
        items.value = section?.items ?? []
        pagination.value = section?.pagination ?? null
        if (syncUrl.value) {
            pagePath.value = section?.path ?? []
        }
        sectionList.value = list ?? null
        filterData.value = filter ?? null
        additionalData.value = additional
        sectionPath.value = source.pathname
        filters.initializeFiltersFromChecked()
        requestUrl.value = deriveRequestUrl()
    }

    const toAbsolute = (url: string | URL | undefined | null) => {
        if (!url) return ''
        const s = url.toString()
        return new URL(s.startsWith('/') || /^https?:/.test(s) ? s : `/${s}`, origin).toString()
    }

    /** Инициализация состояния страницы */
    const initialize = (data: SectionData | null | undefined, initParams: SectionInitParams) => {
        syncUrl.value = !!initParams.syncUrl
        apiUrl.value = toAbsolute(initParams.apiUrl)
        pageInUrl.value = initParams.pageInUrl ?? true
        serviceParams = [...new Set([...BASE_SERVICE_PARAMS, ...(initParams.serviceParams ?? [])])]
        applyPage(data ? JSON.parse(JSON.stringify(data)) : {}, new URL(apiUrl.value))
        publicKey = toCanon(apiUrl.value)
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
            const keep = [filterData.value?.sorting?.requestParam || 'sort', pagination.value?.paginationUrlParam, 'q']
            keep.forEach(k => {
                if (!k) return
                if (state.searchParams.has(k)) target.searchParams.set(k, state.searchParams.get(k)!)
                else target.searchParams.delete(k)
            })
        } else {
            target.search = state.search
        }

        const pageParam = pagination.value?.paginationUrlParam
        if (!pageInUrl.value && pageParam) target.searchParams.delete(pageParam)

        serviceParams.forEach(k => target.searchParams.delete(k))
        return target.pathname + target.search
    }

    /** Загрузка страницы */
    const loadPage = async (url: URL, { append = false, restoreTo }: LoadOptions = {}) => {
        const reqUrl = new URL(url)
        reqUrl.searchParams.set('ajax', 'y')
        const keepUrl = append && !pageInUrl.value

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
            if (res.data.seo && syncUrl.value) pageSeo.value = res.data.seo

            if (syncUrl.value && !keepUrl) {
                const filterUrl = Object.keys(filters.appliedFilters.value).length ? page.filter?.filterUrl : null
                const target = restoreTo ?? toPublicUrl(filterUrl)

                publicKey = toCanon(target)
                requestByPublic.set(publicKey, requestUrl.value!)

                if (!restoreTo && import.meta.client && toCanon(router.currentRoute.value.fullPath) !== publicKey) {
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
        if (!syncUrl.value || toCanon(fullPath) === publicKey) return
        const req = requestByPublic.get(toCanon(fullPath)) ?? fullPath
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

    const loadSection = async (url?: string | URL) => {
        if (url) {
            apiUrl.value = toAbsolute(url)
            filters.clearFilters()
        }
        await loadPage(new URL(baseUrl.value!))
    }

    const loadPagePublic = async (url?: string | URL) => {
        await loadPage(new URL(toAbsolute(url ?? baseUrl.value!)))
    }

    // Создание = инициализация: данные готовы уже при первом рендере (SSR, без hydration mismatch)
    initialize(initialData, params)

    return {
        filterData,
        items,
        pagination,
        sectionList,
        additionalData,
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
        loadSection,
        loadPage: loadPagePublic,
        ...pager,
    }
}

/**
 * Создаёт состояние раздела, инициализирует его данными с сервера и (в sync-режиме)
 * следит за адресом внутри каталога / страницы на основе ИБ.
 * Вызывать один раз, в корневом компоненте раздела, в setup. Замена useSectionInit.
 */
export function provideSectionPage(initialData: SectionData | null | undefined, params: SectionInitParams) {
    const ctx = reactive(createSectionPage(initialData, params))
    provide(sectionPageKey, ctx)

    if (params.syncUrl) {
        onBeforeRouteUpdate((to, from) => {
            if (params.onStopSyncUrl?.(to, from)) return
            ctx.syncFromRoute(to.fullPath)
        })
    }

    return ctx
}

/** Вызывать в дочерних компонентах раздела (фильтры, список, пагинация, сортировка). Замена useSectionStore. */
export function useSectionPage() {
    const ctx = inject(sectionPageKey, null)
    if (!ctx) throw new Error('useSectionPage() вызван вне раздела: нет provideSectionPage() в родителях')
    return ctx
}