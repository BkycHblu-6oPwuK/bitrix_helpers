#!/bin/bash
echo "Starting application..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
