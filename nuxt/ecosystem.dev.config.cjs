require('dotenv').config();

const path = require('path');
const appDir = path.resolve(__dirname);

module.exports = {
    apps: [
        {
            name: 'nuxt-ssr',
            script: path.join(appDir, '.output/server/index.mjs'),
            cwd: appDir,

            exec_mode: 'fork',
            instances: 1,

            node_args: [
                '--max-old-space-size=768',
                ...(process.env.NUXT_DEBUG === 'true' ? ['--expose-gc', '--heapsnapshot-signal=SIGUSR2'] : []),
            ].join(' '),

            autorestart: true,
            max_restarts: 20,
            min_uptime: '10s',
            restart_delay: 2000,

            max_memory_restart: '1000M',

            error_file: path.join(appDir, 'logs/err.log'),
            out_file: path.join(appDir, 'logs/out.log'),
            log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
            merge_logs: true,

            kill_timeout: 20000,
            listen_timeout: 22000,

            env: {
                ...process.env,
                NODE_ENV: 'production',
                NITRO_HOST: '0.0.0.0',
                HOST: '0.0.0.0',
                PORT: 5174,
                NITRO_PORT: 5174,
            },
        },
    ],
};