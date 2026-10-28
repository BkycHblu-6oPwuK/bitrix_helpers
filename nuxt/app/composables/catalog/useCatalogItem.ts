import type { CatalogItemDTO } from '~/types/iblock/catalog'

/**
 * Данные товара, которые НЕ зависят от выбранного торгового предложения:
 * картинки, метка, артикул. Принимает ref, геттер или значение.
 */
export function useCatalogItem(item: MaybeRefOrGetter<CatalogItemDTO | null | undefined>) {
    const images = computed(() => {
        const v = toValue(item)
        if (!v) return null

        const result: string[] = []
        if (v.previewPictureSrc) result.push(v.previewPictureSrc)
        if (v.detailPictureSrc) result.push(v.detailPictureSrc)

        // MORE_PHOTO: значения — файлы ({ src }) или готовые строки.
        // Внимание: в разных проектах DTO свойства называется valuesDto / values — подстройте под свой тип.
        for (const photo of v.properties?.MORE_PHOTO?.valuesDto ?? []) {
            const raw = photo?.value as string | { src?: string } | null | undefined
            const src = typeof raw === 'string' ? raw : raw?.src
            if (src) result.push(src)
        }

        // Set убирает дубли (превью == детальная и т.п.)
        return result.length ? [...new Set(result)] : null
    })
    /** Артикул: свойство ARTNUMBER, если его нет — символьный код */
    const article = computed(() => {
        const v = toValue(item)
        const art = v?.properties?.ARTNUMBER?.valuesDto?.[0]?.value
        return art ? String(art) : (v?.code ?? null)
    })

    return { images, article }
}