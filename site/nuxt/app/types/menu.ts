export type MenuType = 'catalog_menu' | 'bottom_menu' | 'top_menu' | string

export interface MenuItem {
  id: number
  name: string
  code: string
  iblockSectionId: number
  link: string
  children: MenuItem[]
}

export interface MenuData {
  menu: MenuItem[]
}
