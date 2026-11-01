import { defineSitemapEventHandler } from '#imports'
import type { SitemapUrlInput } from '#sitemap/types'

export default defineSitemapEventHandler(async (event) => {
    const res = await event.$fetch<{ status: string; data: { loc: string }[] }>(getApiUrl('sitemap'))

    if (res?.status !== 'success' || !Array.isArray(res.data)) {
        return []
    }

    return res.data satisfies SitemapUrlInput[]
})