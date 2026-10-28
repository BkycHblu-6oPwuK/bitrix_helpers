import type { MaybeRefOrGetter } from 'vue'
import type { BreadcrumbItem } from '~/types/breadcrumbs'

/** Хвост хлебных крошек текущего раздела (без корневого пункта страницы) */
export const usePagePathState = () => useState<BreadcrumbItem[]>('page-path', () => [])

/** Статический префикс хлебных крошек, задаётся на уровне страницы */
const usePagePathPrefixState = () => useState<BreadcrumbItem[]>('page-path-prefix', () => [])

/**
 * Устанавливает статический префикс хлебных крошек текущей страницы
 * и сбрасывает динамический хвост (задаётся section store).
 * Вызывать один раз синхронно в setup страницы.
 */
export function usePagePath(prefix: MaybeRefOrGetter<BreadcrumbItem[]> = [], pagePath: MaybeRefOrGetter<BreadcrumbItem[]> = []) {
  const prefixState = usePagePathPrefixState()
  const tailState = usePagePathState()

  watch(
    () => toValue(prefix),
    val => {
      prefixState.value = val ?? []
    },
    { immediate: true },
  )

  watch(
    () => toValue(pagePath),
    val => {
      tailState.value = val ?? []
    },
    { immediate: true },
  )
}

/**
 * Возвращает полный список хлебных крошек:
 * Главная + статический префикс (из страницы) + динамический хвост (из section store).
 * Возвращает пустой массив, если нет ни одного пункта после «Главной».
 */
export function usePageCrumbs() {
  const prefix = usePagePathPrefixState()
  const tail = usePagePathState()

  return computed<BreadcrumbItem[]>(() => {
    const all = [...prefix.value, ...tail.value]
    if (!all.length) return []
    return [{ title: 'Главная', to: '/' }, ...all]
  })
}
