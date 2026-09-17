import {
    defineEventHandler,
    getRequestURL,
    getHeader,
    setResponseHeader,
    setResponseStatus,
} from 'h3';

export default defineEventHandler((event) => {
    try {
        const cfg = useRuntimeConfig();
        const path = getRequestURL(event).pathname || '';

        if (
            path.startsWith('/api/') ||
            path.startsWith('/_nuxt/') ||
            path.startsWith('/__nuxt') ||
            path.startsWith('/assets/') ||
            path.startsWith('/local_assets/')
        ) {
            return;
        }

        const buildDate = new Date(cfg.buildAt);
        if (Number.isNaN(buildDate.getTime())) return;

        setResponseHeader(event, 'Last-Modified', buildDate.toUTCString());

        const ims = getHeader(event, 'if-modified-since');

        if (ims) {
            const clientDate = new Date(ims);

            if (!Number.isNaN(clientDate.getTime()) && clientDate >= buildDate) {
                setResponseStatus(event, 304);
                return '';
            }
        }
    } catch { }
});
