export interface PaginationPage {
  pageNumber: number
  isSelected: boolean
}

export interface PaginationDTO {
  pages: PaginationPage[]
  pageSize: number
  currentPage: number
  pageCount: number
  paginationUrlParam: string
}