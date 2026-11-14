# syntax=docker/dockerfile:1.7
ARG FRANKENPHP_VERSION=1.13.5
ARG PHP_VERSION=8.5
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION} AS opencv
ARG OPENCV_VERSION=5.1.0
ARG OPENCV_PHP_REVISION=2960c8e1f27d911f311354ffbf5d95ff7e64fa0e
RUN apt-get update && apt-get install -y --no-install-recommends build-essential cmake ninja-build git ca-certificates pkg-config autoconf \
    && rm -rf /var/lib/apt/lists/*
RUN git clone --depth 1 --branch ${OPENCV_VERSION} https://github.com/opencv/opencv.git /src/opencv \
    && cmake -S /src/opencv -B /build/opencv -G Ninja \
        -DCMAKE_BUILD_TYPE=Release -DCMAKE_INSTALL_PREFIX=/opt/opencv \
        -DBUILD_LIST=core,imgproc,imgcodecs,geometry,objdetect \
        -DBUILD_SHARED_LIBS=ON -DOPENCV_GENERATE_PKGCONFIG=ON \
        -DBUILD_JPEG=ON -DBUILD_PNG=ON -DBUILD_TIFF=ON -DBUILD_WEBP=ON -DBUILD_ZLIB=ON \
        -DWITH_OPENEXR=OFF -DWITH_OPENJPEG=OFF -DWITH_JASPER=OFF -DWITH_AVIF=OFF -DWITH_JPEGXL=OFF \
        -DWITH_FFMPEG=OFF -DWITH_GSTREAMER=OFF -DWITH_V4L=OFF -DWITH_GTK=OFF -DWITH_QT=OFF \
        -DWITH_OPENCL=OFF -DWITH_ITT=OFF -DWITH_PROTOBUF=OFF -DWITH_FLATBUFFERS=OFF \
        -DBUILD_TESTS=OFF -DBUILD_PERF_TESTS=OFF -DBUILD_EXAMPLES=OFF -DBUILD_DOCS=OFF \
        -DBUILD_opencv_apps=OFF -DBUILD_JAVA=OFF -DBUILD_opencv_python3=OFF -DBUILD_opencv_js=OFF \
    && cmake --build /build/opencv --parallel 4 \
    && cmake --install /build/opencv

RUN git init /src/ext && cd /src/ext \
    && git remote add origin https://github.com/survos/opencv-php.git \
    && git fetch --depth 1 origin ${OPENCV_PHP_REVISION} && git checkout --detach FETCH_HEAD \
    && export PKG_CONFIG_PATH=/opt/opencv/lib/pkgconfig \
    && phpize && ./configure --with-opencv && make -j4 \
    && cp modules/opencv.so "$(php-config --extension-dir)/"

FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION} AS base
WORKDIR /app
RUN install-php-extensions exif gd intl pcntl pdo_pgsql pdo_sqlite zip
COPY --from=opencv /opt/opencv/lib/ /opt/opencv/lib/
COPY --from=opencv /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
RUN echo /opt/opencv/lib > /etc/ld.so.conf.d/opencv.conf && ldconfig && docker-php-ext-enable opencv && php --ri opencv
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
