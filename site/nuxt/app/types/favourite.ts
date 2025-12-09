export interface FavouriteResponse {
    count: number,
    items: number[]
}

export interface FavouriteToggleResponse {
    action: "added" | "removed" | string,
    isFavorite: boolean,
    count: number
}