import type { SectionDTO } from "./iblock"
import type { CatalogDTO, CatalogItemDTO } from "./iblock/catalog"
import type { SectionsDTO } from "./iblock/page"
import type { PageData, PageDataWithElement } from "./page"

/** Тип ответа API для страницы списка разделов */
export type SectionsPageApiResponse = PageData<SectionsDTO>

/** Тип ответа API для страницы каталога с товарами */
export type CatalogPageApiResponse = PageData<CatalogDTO>

export type CatalogDetailPageApiResponse = PageDataWithElement<CatalogItemDTO>

/** Тип ответа API для результата поиска по каталогу */
export type CatalogSearchApiResponse = {
    products: CatalogItemDTO[]
    sections: SectionDTO[]
}

