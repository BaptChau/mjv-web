# =============================================================================
# Stage 1: Composer dependencies
# =============================================================================
FROM composer:2 AS composer-deps

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

COPY . /app

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && composer run-script post-install-cmd --no-interaction || true

# =============================================================================
# Stage 2: Node asset build
# =============================================================================
FROM node:20-alpine AS node-build

WORKDIR /app

COPY package.json package-lock.json webpack.config.js postcss.config.mjs ./
COPY assets ./assets

# We need the symfony UX vendor assets for the build
COPY --from=composer-deps /app/vendor/symfony/ux-turbo/assets ./vendor/symfony/ux-turbo/assets
COPY --from=composer-deps /app/vendor/symfony/stimulus-bundle/assets ./vendor/symfony/stimulus-bundle/assets

# Install controllers.json for Stimulus bridge
COPY assets/controllers.json ./assets/controllers.json

RUN npm ci --prefer-offline && npm run build

# =============================================================================
# Stage 3: Production PHP-FPM image
# =============================================================================
FROM php:8.2-fpm-alpine AS app-prod

# Install only runtime dependencies (no build tools)
RUN apk add --no-cache \
    postgresql-libs \
    libzip \
    icu-libs \
    && apk add --no-cache --virtual .build-deps \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql zip intl opcache \
    && apk del .build-deps

# PHP production tuning
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-fpm-prod.conf /usr/local/etc/php-fpm.d/zz-prod.conf

WORKDIR /app

# Copy application from build stages
COPY --from=composer-deps /app/vendor ./vendor
COPY --from=composer-deps /app/composer.json ./composer.json
COPY --from=composer-deps /app/composer.lock ./composer.lock
COPY --from=composer-deps /app/symfony.lock ./symfony.lock
COPY --from=node-build /app/public/build ./public/build

# Copy application source (excluding what .dockerignore filters)
COPY bin ./bin
COPY config ./config
COPY migrations ./migrations
COPY public ./public/
COPY src ./src
COPY templates ./templates
COPY translations ./translations
COPY .env ./.env

# Re-copy build assets on top (public/build was added above, but public/ COPY may overwrite)
COPY --from=node-build /app/public/build ./public/build

# Warmup cache as root before switching user
RUN php bin/console cache:warmup --env=prod --no-debug || true

# Create non-root user
RUN addgroup -g 1000 -S appuser && adduser -u 1000 -S appuser -G appuser \
    && chown -R appuser:appuser /app

USER appuser

EXPOSE 9000

CMD ["php-fpm"]

# =============================================================================
# Stage 4: Dev image (php cli with all dev tools)
# =============================================================================
FROM php:8.2-cli AS app-dev

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libicu-dev \
    curl ca-certificates \
    && docker-php-ext-install pdo pdo_pgsql zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN git config --global --add safe.directory /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
