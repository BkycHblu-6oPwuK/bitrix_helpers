const memMB = () => {
    const m = process.memoryUsage();
    return `heap=${Math.round(m.heapUsed / 1024 / 1024)}/${Math.round(m.heapTotal / 1024 / 1024)}MB`;
};

export default defineEventHandler((event) => {
    const { debug } = useRuntimeConfig();
    if (!debug) return;

    const method = event.node.req.method ?? '-';
    const url = event.node.req.url ?? '-';
    const ts = new Date().toISOString();

    if (url.startsWith('/_nuxt/')) return;

    const startMs = Date.now();
    const memBefore = memMB();

    event.node.res.on('finish', () => {
        const status = event.node.res.statusCode;
        const memAfter = memMB();
        const ms = Date.now() - startMs;
        console.log(`[${ts}] REQ ${method} ${url} → ${status} ${ms}ms | before=${memBefore} after=${memAfter}`);
        if (status >= 500) {
            console.error(`[${ts}] ERROR ${status} ${method} ${url} ${ms}ms | mem=${memAfter}`);
        }
    });
});
