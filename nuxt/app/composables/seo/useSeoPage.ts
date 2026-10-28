import type { MaybeRefOrGetter } from 'vue'
import type { SeoData } from '~/types/seo'

export const usePageSeoState = () => useState<SeoData | null>('page-seo', () => null)

function getCanonicalUrl() {
    const url = useRequestURL();
    return `${url.protocol}//${url.hostname}${url.pathname}`
}

/**
 * Реактивно устанавливает SEO мета-теги страницы.
 * Поддерживает как статические данные (plain object / undefined), так и реактивные (ref / getter).
 * При использовании getter-а реагирует на изменения (например, при AJAX-навигации через section store).
 * Вызывать один раз синхронно в setup, не внутри watch и не после await.
 */
export function useSeoPage(
    seo: MaybeRefOrGetter<SeoData | null | undefined> = undefined,
    defaults: Partial<SeoData> = {},
) {
    const state = usePageSeoState()

    watch(() => toValue(seo), val => { state.value = val ?? null }, { immediate: true })

    const title = computed(() => state.value?.title || defaults.title)
    const description = computed(() => state.value?.description || defaults.description)

    useSeoMeta({
        title,
        description,
        ogTitle: title,
        ogDescription: description,
    })

    useHead({
        link: [
            { rel: 'canonical', href: getCanonicalUrl() }
        ]
    })
}