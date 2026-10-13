/** Тег поиска с URL фильтра каталога */
export interface SearchTagDTO {
  label: string
  url: string
}

/** Легковесный товар в саджесте */
export interface SearchProductDTO {
  id: number
  name: string
  detailPageUrl: string
  previewPictureSrc: string | null
  previewText: string
  typeMasla: string
}

/** Раздел каталога в саджесте */
export interface SearchSectionDTO {
  id: number
  name: string
  code: string
  url: string
  pictureSrc: string | null
}

/** Ответ /search/init */
export interface SearchInitDTO {
  tags: SearchTagDTO[]
  products: SearchProductDTO[]
  sections: SearchSectionDTO[]
}

/** Ответ /search/suggest/:query */
export interface SearchSuggestDTO {
  tags: string[]
  products: SearchProductDTO[]
  sections: SearchSectionDTO[]
}
