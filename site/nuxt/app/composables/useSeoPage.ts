import type { SeoData } from "~/types/seo"

export function useSeoPage(seo?: SeoData) {
  if (!seo) return
  useSeoMeta({
    title: seo.title,
    description: seo.description,
  })
}
