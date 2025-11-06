export type MenuType = 'catalog_menu' | 'bottom_menu' | string

export interface MenuItem {
  ID?: string
  NAME: string
  CODE?: string
  LINK?: string
  URL?: string
  SECTION_PAGE_URL?: string
  IBLOCK_SECTION_ID?: string | null
  CHILDREN?: MenuItem[]
}

export interface MenuBlock {
  LINK: string
  NAME: MenuItem[]
  CHILDREN: MenuItem[]
}

export interface MenuData {
  menu: MenuBlock[]
}
