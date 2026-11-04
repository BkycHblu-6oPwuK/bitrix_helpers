const memMB = (): string => {
    // process.memoryUsage есть только в Node.js/Bun (не в Edge/Serverless)
    if (typeof process === 'undefined' || !process.memoryUsage) return 'heap=N/A'

    const { heapUsed, heapTotal } = process.memoryUsage()
    return `heap=${Math.round(heapUsed / 1024 / 1024)}/${Math.round(heapTotal / 1024 / 1024)}MB`
}

export default defineNitroPlugin((nitroApp) => {
    if (!useRuntimeConfig().debug) return

    nitroApp.hooks.hook('request', (event) => {
        if (event.path.startsWith('/_nuxt/')) return

        event.context._log = {
            startedAt: performance.now(),
            ts: new Date().toISOString(),
            memBefore: memMB(),
        }
    })

    nitroApp.hooks.hook('afterResponse', (event) => {
        const log = event.context._log as
            | { startedAt: number; ts: string; memBefore: string }
            | undefined
        if (!log) return

        const status = getResponseStatus(event)
        const ms = Math.round(performance.now() - log.startedAt)
        const memAfter = memMB()
        const { method, path } = event

        console.log(
            `[${log.ts}] REQ ${method} ${path} → ${status} ${ms}ms | before=${log.memBefore} after=${memAfter}`,
        )

        if (status >= 500) {
            console.error(`[${log.ts}] ERROR ${status} ${method} ${path} ${ms}ms | mem=${memAfter}`)
        }
    })
})