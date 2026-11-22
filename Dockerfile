# syntax=docker/dockerfile:1.7
ARG FRANKENPHP_VERSION=1.13.5
ARG PHP_VERSION=8.5
# OpenCV (perspective correction) is not built here: it made every deploy rebuild it from source, and
# the app skips correction without it. Git history has the build stage.
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION} AS base
WORKDIR /app
RUN install-php-extensions exif gd intl pcntl pdo_pgsql pdo_sqlite zip
COPY Caddyfile /etc/caddy/Caddyfile
COPY docker/php.ini $PHP_INI_DIR/conf.d/app.ini

FROM base AS build
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 APP_ENV=prod APP_DEBUG=0
COPY composer.json composer.lock symfony.lock ./
RUN --mount=type=cache,target=/root/.cache/composer composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN php bin/apply-mistral-ocr-fix.php && composer dump-autoload --optimize --no-dev --no-scripts \
    && mkdir -p var/media var/cache var/log var/elastic-spool

FROM base AS prod
ENV APP_ENV=prod APP_DEBUG=0
COPY --from=build --chown=www-data:www-data /app /app
EXPOSE 5000
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
