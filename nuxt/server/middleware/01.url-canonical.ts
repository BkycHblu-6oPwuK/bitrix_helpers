/**
 * Middleware для каноникализации URL:
 * - удаляет дублирующие слэши
 * - приводит к нижнему регистру
 * - добавляет/убирает завершающий слэш (по правилам)
 * - редиректит с www на без www
 * - редиректит с http на https (если в конфиге указано https)
 */

import type { H3Event } from 'h3';
import { getRequestHeader, getRequestURL, sendRedirect } from 'h3';

function normalizePath(path: string): string {
    let normalized = (path || '/').toLowerCase().replace(/\/+/g, '/');

    if (!normalized.startsWith('/')) {
        normalized = `/${normalized}`;
    }

    const isApiRoute = normalized.startsWith('/api/');
    /**
     * страницы - слеш на конце
     * api - без слеша на конце
     */
    if (!isApiRoute && !normalized.endsWith('/') && normalized.length > 1 && !/\.[^/]+$/.test(normalized)) {
        normalized += '/';
    }

    return normalized;
}

function applyLegacyRoot(path: string): string {
    if (/^\/(?:index|home)\.(?:php|html?)\/?$/i.test(path)) return '/';
    return path;
}

function getHostFromEvent(event: H3Event): string {
    return (
        getRequestHeader(event, 'x-forwarded-host')?.split(',')[0]?.trim() ||
        getRequestHeader(event, 'host') ||
        getRequestURL(event).host ||
        ''
    );
}

function getProtoFromEvent(event: H3Event): string {
    return (
        getRequestHeader(event, 'x-forwarded-proto')?.split(',')[0]?.trim() ||
        getRequestURL(event).protocol.replace(':', '') ||
        'https'
    );
}

function parseRawReqUrl(reqUrl: string | undefined): {
    pathname: string;
    search: string;
    hash: string;
} {
    if (!reqUrl) return { pathname: '/', search: '', hash: '' };
    let rest = reqUrl;
    let hash = '';
    const hi = rest.indexOf('#');
    if (hi >= 0) {
        hash = rest.slice(hi);
        rest = rest.slice(0, hi);
    }
    const qi = rest.indexOf('?');
    if (qi >= 0) {
        const pathname = rest.slice(0, qi) || '/';
        const search = rest.slice(qi);
        return { pathname, search, hash };
    }
    return { pathname: rest || '/', search: '', hash };
}

function publicOrigin(event: H3Event): string {
    const host = getHostFromEvent(event);
    const proto = getProtoFromEvent(event);
    return `${proto}://${host}`;
}

export default defineEventHandler((event) => {
    const method = event.node.req.method;
    if (method !== 'GET' && method !== 'HEAD') return;

    const raw = parseRawReqUrl(event.node.req.url);
    const rawPathname = raw.pathname;

    if (
        rawPathname.startsWith('/_nuxt/') ||
        rawPathname.startsWith('/__nuxt')
    ) {
        return;
    }

    let collapsed = rawPathname.replace(/\/+/g, '/');
    collapsed = applyLegacyRoot(collapsed);

    if (
        collapsed.startsWith('/assets') ||
        collapsed.startsWith('/local_assets')
    ) {
        return;
    }

    const canonicalPath = normalizePath(collapsed);
    const search = raw.search || '';
    const hash = raw.hash || '';
    const origin = publicOrigin(event);
    const host = getHostFromEvent(event);
    const proto = getProtoFromEvent(event);

    const cfg = useRuntimeConfig();
    const canonicalFromCfg = cfg.effectiveHost || cfg.public?.baseUrl || '';
    let canonicalHost = '';
    let canonicalProto = '';
    try {
        const u = canonicalFromCfg ? new URL(canonicalFromCfg) : null;
        canonicalHost = u?.host || '';
        canonicalProto = (u?.protocol || '').replace(':', '');
    } catch { }

    const absolute = `${origin}${canonicalPath}${search}${hash}`;

    if (host.startsWith('www.')) {
        const newHost = host.replace(/^www\./, '');
        return sendRedirect(event, `https://${newHost}${canonicalPath}${search}${hash}`, 301);
    }

    if (proto === 'http' && canonicalHost && host === canonicalHost && canonicalProto === 'https') {
        return sendRedirect(event, `https://${host}${canonicalPath}${search}${hash}`, 301);
    }

    if (canonicalPath !== rawPathname) {
        return sendRedirect(event, absolute, 301);
    }
});
