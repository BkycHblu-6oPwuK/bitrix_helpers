import type { SeoData } from "~/types/seo"

/**
 * Композабл для установки SEO мета-тегов страницы
 * Использует встроенный useSeoMeta из Nuxt для управления meta-тегами
 * 
 * @param seo - Объект с SEO данными (title, description)
 */
export function useSeoPage(seo?: SeoData) {
  if (!seo) return
  
  // Устанавливаем title и description для страницы
  useSeoMeta({
    title: seo.title,
    description: seo.description,
  })
}
