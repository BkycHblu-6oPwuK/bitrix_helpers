import type { SectionData } from '~/types/iblock/page'
import type { SectionInitParams } from '~/stores/section'

/**
 * Инициализирует стор данными с сервера и (в sync-режиме) следит за адресом внутри каталога/странице на основе иб (ну новости еще).
 * Вызывать один раз, в корневом компоненте каталога, до дочерних компонентов.
 */
export function useSectionInit<T extends SectionData>(initialData: T, params: SectionInitParams) {
    const store = useSectionStore()
    store.initialize(initialData, params)

    if (params.syncUrl) {
        // Назад/Вперёд и ссылки внутри каталога; хук снимается сам при размонтировании
        onBeforeRouteUpdate((to) => { store.syncFromRoute(to.fullPath) })
    }
}