import type { PropertyItemDTO } from "./property"

export interface ElementDTO {
  id: number
  code: string
  name: string

  previewText: string
  previewPicture: string
  detailText: string
  detailPicture: string

  detailPageUrl: string
  listPageUrl: string
  dateCreate: string

  properties: PropertyItemDTO[]
}
