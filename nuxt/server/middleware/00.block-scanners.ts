const BLOCKED_EXTENSIONS = /\.(php\d*|asp|aspx|jsp|cgi|env|git|svn|htaccess|htpasswd|DS_Store|bak|sql|cfg|sh|bash|rb|py)(\?.*)?$/i;

const BLOCKED_PATHS = /\/(vendor|composer|eval-stdin|xmlrpc|wp-login|wp-admin|\.well-known\/acme|actuator|\.env|\.git|\.svn|phpmyadmin|myadmin|pma)/i;

export default defineEventHandler((event) => {
    const url = event.node.req.url ?? '';
    const path = url.split('?')[0] ?? '';

    if (BLOCKED_EXTENSIONS.test(path) || BLOCKED_PATHS.test(path)) {
        throw createError({ statusCode: 404 });
    }
})