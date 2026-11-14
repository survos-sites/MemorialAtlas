web: frankenphp run --config /etc/caddy/Caddyfile
worker: bash bin/search-worker
processing: php bin/console messenger:consume processing --no-interaction --memory-limit=384M
