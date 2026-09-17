export default defineNitroPlugin((nitroApp) => {
    nitroApp.hooks.hook('sitemap:output' as any, (ctx: { sitemap: string }) => {
        ctx.sitemap = ctx.sitemap.replace(
            /<loc>([^<]+?)<\/loc>/g,
            (_, url: string) => {
                if (!url.endsWith('/') && !/\.[a-zA-Z0-9]+$/.test(url.replace(/^https?:\/\/[^/]+/, ''))) {
                    return `<loc>${url}/</loc>`;
                }
                return `<loc>${url}</loc>`;
            },
        );
    });
});
