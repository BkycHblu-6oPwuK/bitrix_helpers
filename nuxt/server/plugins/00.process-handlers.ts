import { writeSync } from 'node:fs';

/**
 * Нитро-плагин: перехватывает SIGTERM (убийство PM2 по памяти),
 * uncaughtException, unhandledRejection — и логирует их в stdout.
 *
 * Для сигналов используется синхронная запись (fs.writeSync),
 * которая не теряется даже если process.exit() вызывается сразу после.
 */
export default defineNitroPlugin(() => {
    const { debug } = useRuntimeConfig();
    if (!debug) return;

    const ts = () => new Date().toISOString();
    const memStr = () => {
        const m = process.memoryUsage();
        return `RSS=${Math.round(m.rss / 1024 / 1024)}MB Heap=${Math.round(m.heapUsed / 1024 / 1024)}/${Math.round(m.heapTotal / 1024 / 1024)}MB Ext=${Math.round((m.external ?? 0) / 1024 / 1024)}MB`;
    };

    // Синхронная запись в stdout — не теряется при быстром process.exit()
    const syncLog = (msg: string) => {
        try { writeSync(1, msg + '\n'); } catch { }
    };

    // ── Фатальные ошибки Node.js ──────────────────────────────────────────
    process.on('uncaughtException', (err) => {
        syncLog(`[${ts()}] [FATAL] uncaughtException: ${err?.stack ?? err}`);
    });

    process.on('unhandledRejection', (reason) => {
        const msg = reason instanceof Error ? reason.stack : String(reason);
        syncLog(`[${ts()}] [FATAL] unhandledRejection: ${msg}`);
    });

    // ── Сигналы завершения (SIGTERM = pm2 max_memory_restart / reload) ────
    const onShutdown = (sig: string) => {
        // Синхронная запись гарантирует что лог дойдёт до PM2 до process.exit()
        syncLog(`[${ts()}] [SHUTDOWN] Received ${sig}. ${memStr()}`);
    };
    process.once('SIGTERM', () => onShutdown('SIGTERM'));
    process.once('SIGINT', () => onShutdown('SIGINT'));

    const memTimer = setInterval(() => {
        const before = memStr();
        // --expose-gc позволяет принудительно запустить GC и замерить реальный leak
        if (typeof (global as any).gc === 'function') {
            (global as any).gc();
            console.log(`[${ts()}] [MEM] before-gc=${before} after-gc=${memStr()}`);
        } else {
            console.log(`[${ts()}] [MEM] ${memStr()}`);
        }
    }, 30_000);
    // Не держим event loop только из-за таймера
    if (memTimer.unref) memTimer.unref();
});
