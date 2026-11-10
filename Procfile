web: vendor/bin/heroku-php-nginx -C nginx.conf public/
worker: while true; do php bin/console elastic:spool:flush; sleep 10; done
