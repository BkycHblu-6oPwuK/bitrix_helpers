import type { PropertyItemDTO } from "./property"

export interface ElementDTO {
  id: number
  code: string
  name: string

  previewText?: string | null
  previewPicture?: string | null
  detailText?: string | null
  detailPicture?: string | null

  detailPageUrl?: string | null
  listPageUrl?: string | null
  dateCreate?: string | null

  properties: PropertyItemDTO[]
}
