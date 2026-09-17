/**
 * Глобальный middleware для нормализации URL (удаление лишних слэшей).
 * В отличие от серверного middleware, не делает редирект, а просто исправляет URL в роутере.
 */

import { defineNuxtRouteMiddleware, navigateTo } from '#imports';

export default defineNuxtRouteMiddleware((to) => {
    if (import.meta.server) return;

    const collapsed = to.path.replace(/\/+/g, '/');

    const hasExtension = /\.[^/]+$/.test(collapsed);
    const needsSlash = !hasExtension && collapsed.length > 1 && !collapsed.endsWith('/');
    // но на клиенте делаем слеш в конце
    const normalized = needsSlash ? collapsed + '/' : collapsed;

    if (normalized === to.path) return;

    return navigateTo(
        { path: normalized || '/', query: to.query, hash: to.hash },
        { replace: true },
    );
});
