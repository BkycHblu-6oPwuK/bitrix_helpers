import type { CatalogItemDTO } from "./iblock/catalog"
import type { PageData } from "./iblock/content"
import type { SectionData } from "./iblock/page"
import type { PaginationDTO } from "./pagination"

export interface FavouriteResponse {
    count: number,
    items: number[]
}

export interface FavouriteToggleResponse {
    action: "added" | "removed" | string,
    isFavorite: boolean,
    count: number
}

export interface FavouritePageDTO extends SectionData<null, null, CatalogItemDTO> { }

export type FavouritePageApiResponse = PageData<FavouritePageDTO>