import type { CatalogItemDTO } from "~/types/iblock/catalog"

/**
 * Композабл для работы с элементом каталога
 * Предоставляет вычисляемые свойства для удобного доступа к данным элемента
 */
export function useCatalogItem(item: Ref<CatalogItemDTO | null>) {
    const isAvailable = computed(() => {
        const v = item.value
        if (!v) return false

        return v.catalog?.available
            ?? v.preselectedOffer?.catalog.available
            ?? v.offers?.[0]?.catalog.available
            ?? false
    })

    const images = computed(() => {
        const v = item.value
        if (!v) return null

        const images: string[] = []

        if (v.previewPictureSrc) {
            images.push(v.previewPictureSrc)
        }
        if (v.detailPictureSrc && v.detailPictureSrc !== v.previewPictureSrc) {
            images.push(v.detailPictureSrc)
        }
        if (v.properties?.MORE_PHOTO) {
            const morePhotos = Array.isArray(v.properties.MORE_PHOTO)
                ? v.properties.MORE_PHOTO
                : [v.properties.MORE_PHOTO]
            morePhotos.forEach((photo: any) => {
                if (photo?.pictureSrc) {
                    images.push(photo.pictureSrc)
                }
            })
        }

        return images.length > 0 ? images : null
    })

    const price = computed(() => {
        const v = item.value
        if (!v) return null

        if (v.preselectedOffer && v.preselectedOffer.prices.length > 0) {
            const price = v.preselectedOffer.prices.find(p => p.catalogGroup?.base === true) || v.preselectedOffer.prices[0];
            return price;
        }

        if (v.offers && v.offers.length > 0 && v.offers[0]) {
            const firstOffer = v.offers[0];
            const price = firstOffer.prices.find(p => p.catalogGroup?.base === true) || firstOffer.prices[0];
            return price;
        }

        if (v.prices && v.prices.length > 0) {
            const price = v.prices.find(p => p.catalogGroup?.base === true) || v.prices[0];
            return price;
        }

        return null;
    })

    return { isAvailable, images, price }
}
