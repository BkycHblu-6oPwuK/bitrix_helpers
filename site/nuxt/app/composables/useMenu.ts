import type { MenuData } from '~/types/menu'

/**
 * Композабл для получения данных меню сайта
 * В текущей реализации возвращает статические данные
 * TODO: В будущем можно заменить на API запрос для динамического меню
 * 
 * @returns Объект с верхним и нижним меню
 */
export const useMenu = () => {
  // Верхнее меню (основная навигация)
  const topMenu: MenuData = {
    menu: [
      {
        id: 1,
        name: 'Статьи',
        link: '/articles',
        children: [],
        code: '',
        iblockSectionId: 0,
      },
    ],
  }

  // Нижнее меню (футер с подразделами)
  const bottomMenu: MenuData = {
    menu: [
      {
        id: 10,
        name: 'О магазине',
        link: '#',
        code: '',
        iblockSectionId: 0,
        children: [
          {
            id: 11,
            name: 'О компании',
            link: '/about-company',
            children: [],
            code: '',
            iblockSectionId: 0,
          },
          {
            id: 12,
            name: 'Отзывы',
            link: '/reviews',
            children: [],
            code: '',
            iblockSectionId: 0,
          },
        ],
      },
      {
        id: 20,
        name: 'Клиентам',
        link: '#',
        code: '',
        iblockSectionId: 0,
        children: [
          {
            id: 21,
            name: 'Политика конфиденциальности',
            link: '/policy',
            children: [],
            code: '',
            iblockSectionId: 0,
          },
          {
            id: 22,
            name: 'Карта сайта',
            link: '/sitemap',
            children: [],
            code: '',
            iblockSectionId: 0,
          },
        ],
      },
    ],
  }

  return { topMenu, bottomMenu }
}