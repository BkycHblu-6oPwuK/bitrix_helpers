/**
 * Универсальный тип SEO-данных, возвращаемых с API.
 */
export interface SeoData {
    /** <title> страницы */
    title: string

    /** <meta name="description"> */
    description: string

    /** <meta name="keywords"> */
    keywords?: string

    /** Каноническая ссылка (<link rel="canonical">) */
    canonical?: string

    /** <h1> страницы */
    h1?: string

    /** Seo html текст страницы */
    text?: string

    /** Open Graph метаданные (для соцсетей) */
    openGraph?: {
        /** og:title */
        title?: string
        /** og:description */
        description?: string
        /** og:image */
        image?: string
        /** og:type (например, website, article) */
        type?: string
        /** og:url */
        url?: string
    }

    /** Произвольные дополнительные метатеги */
    meta?: Record<string, string | number | boolean>
}
